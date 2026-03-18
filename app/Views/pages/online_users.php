<div class="online-users-container">

    <h1 class="online-title">Usuarios Online</h1>
    <p class="online-subtitle">Usuarios activos en los últimos 10 minutos</p>

    <div id="online-users-list" class="online-users-list">
        <div class="loading">Cargando usuarios...</div>
    </div>

</div>

<script>
fetch('/?api=online_users')
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('online-users-list');
        container.innerHTML = '';

        if (!data.users || data.users.length === 0) {
            container.innerHTML = '<p class="loading">No hay usuarios online.</p>';
            return;
        }

        data.users.forEach(u => {
            const card = document.createElement('div');
            card.className = 'online-user-card';

            const avatar = document.createElement('div');
            avatar.className = 'user-avatar';
            avatar.textContent = u.name.charAt(0).toUpperCase();

            const info = document.createElement('div');
            info.className = 'user-info';

            const name = document.createElement('div');
            name.className = 'user-name';
            name.textContent = u.name;

            const status = document.createElement('div');
            status.className = 'user-status';

            const dot = document.createElement('span');
            dot.className = 'status-dot ' + (u.status === 'online' ? 'status-online' : 'status-away');

            status.appendChild(dot);
            status.append(u.customStatus);

            info.appendChild(name);
            info.appendChild(status);

            card.appendChild(avatar);
            card.appendChild(info);

            container.appendChild(card);
        });
    });
</script>
