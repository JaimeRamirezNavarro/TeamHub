<?php
/**
 * Gather API Client
 * Cliente PHP para interactuar con la API HTTP de Gather.town
 */

require_once __DIR__ . '/gather_config.php';

class GatherAPI {
    private $apiKey;
    private $baseUrl;
    
    public function __construct($apiKey = null) {
        $this->apiKey = $apiKey ?: GATHER_API_KEY;
        $this->baseUrl = GATHER_API_URL;
    }
    
    /**
     * Realizar una petición HTTP a la API de Gather
     */
    private function makeRequest($method, $endpoint, $data = null) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        
        $headers = [
            'Content-Type: application/json',
            'apiKey: ' . $this->apiKey
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => GATHER_TIMEOUT,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method
        ]);
        
        if ($data !== null && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Error en petición a Gather API: " . $error);
        }
        
        if ($httpCode >= 400) {
            throw new Exception("Error HTTP $httpCode: " . $response);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Crear un nuevo espacio en Gather
     */
    public function createSpace($name, $sourceSpace = null, $isAffiliateSpace = false) {
        $data = [
            'name' => $name,
            'sourceSpace' => $sourceSpace,
            'isAffiliateSpace' => $isAffiliateSpace
        ];
        
        return $this->makeRequest('POST', '/spaces', $data);
    }
    
    /**
     * Obtener información de un espacio
     */
    public function getSpace($spaceId) {
        return $this->makeRequest('GET', "/spaces/{$spaceId}");
    }
    
    /**
     * Obtener el mapa de un espacio
     */
    public function getMap($spaceId, $mapId = null) {
        $endpoint = "/spaces/{$spaceId}/maps";
        if ($mapId) {
            $endpoint .= "/{$mapId}";
        }
        return $this->makeRequest('GET', $endpoint);
    }
    
    /**
     * Actualizar el mapa de un espacio
     */
    public function setMap($spaceId, $mapId, $mapContent) {
        return $this->makeRequest('POST', "/spaces/{$spaceId}/maps/{$mapId}", $mapContent);
    }
    
    /**
     * Añadir objetos al mapa (por ejemplo, post-its con información del proyecto)
     */
    public function addMapObjects($spaceId, $mapId, $objects) {
        $data = [
            'mapId' => $mapId,
            'objects' => $objects
        ];
        return $this->makeRequest('POST', "/spaces/{$spaceId}/maps/{$mapId}/objects", $data);
    }
    
    /**
     * Obtener la lista de invitados (guestlist) de un espacio
     */
    public function getGuestList($spaceId) {
        return $this->makeRequest('GET', "/spaces/{$spaceId}/guestlist");
    }
    
    /**
     * Actualizar la lista de invitados
     */
    public function updateGuestList($spaceId, $guestlist) {
        return $this->makeRequest('POST', "/spaces/{$spaceId}/guestlist", $guestlist);
    }
    
    /**
     * Añadir un usuario a la lista de invitados
     */
    public function addGuest($spaceId, $email, $name = '', $role = 'default') {
        $guestlist = $this->getGuestList($spaceId);
        $guestlist[$email] = [
            'name' => $name,
            'role' => $role
        ];
        return $this->updateGuestList($spaceId, $guestlist);
    }
    
    /**
     * Remover un usuario de la lista de invitados
     */
    public function removeGuest($spaceId, $email) {
        $guestlist = $this->getGuestList($spaceId);
        if (isset($guestlist[$email])) {
            unset($guestlist[$email]);
            return $this->updateGuestList($spaceId, $guestlist);
        }
        return false;
    }
    
    /**
     * Crear un espacio de Gather vinculado a un proyecto de TeamHub
     */
    public function createTeamSpace($teamName, $teamDescription) {
        // Nombre limpio para el espacio
        $spaceName = preg_replace('/[^a-zA-Z0-9\s]/', '', $teamName);
        $spaceName = str_replace(' ', '-', $spaceName);
        
        try {
            $space = $this->createSpace($spaceName);
            
            // Añadir un objeto de texto con la descripción del proyecto
            if (!empty($teamDescription) && isset($space['id'])) {
                $mapId = $space['maps'][0] ?? 'main';
                $this->addMapObjects($space['id'], $mapId, [
                    [
                        'type' => 6, // Text object
                        'x' => 5,
                        'y' => 5,
                        'properties' => [
                            'text' => "Proyecto: {$teamName}\n\n{$teamDescription}"
                        ]
                    ]
                ]);
            }
            
            return $space;
        } catch (Exception $e) {
            error_log("Error creando espacio Gather: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sincronizar miembros de TeamHub con la guestlist de Gather
     */
    public function syncTeamMembers($spaceId, $members) {
        $guestlist = [];
        
        foreach ($members as $member) {
            $guestlist[$member['email']] = [
                'name' => $member['username'],
                'role' => $member['role'] === 'admin' ? 'builder' : 'default'
            ];
        }
        
        return $this->updateGuestList($spaceId, $guestlist);
    }
    
    /**
     * Generar URL de invitación para un espacio
     */
    public function getSpaceUrl($spaceId) {
        return "https://app.gather.town/app/{$spaceId}";
    }
}