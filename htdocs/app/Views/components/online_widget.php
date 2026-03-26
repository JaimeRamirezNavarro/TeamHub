<a href="<?= BASE_PATH ?>/online-users" class="online-widget-link">
    <div class="online-widget-left">
        <span class="online-pulse"></span>
        <div>
            <div class="online-widget-title">Activos ahora</div>
            <div class="online-widget-sub" id="widget-status-text">Cargando...</div>
        </div>
    </div>
    <span class="online-count-badge" id="widget-online-count">—</span>
</a>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const fetchOnlineUsers = () => {
        fetch(`${window.TeamHub_BaseUrl || ""}/api/online-users`)
            .then(res => res.json())
            .then(data => {
                const countBadge = document.getElementById('widget-online-count');
                const statusText = document.getElementById('widget-status-text');
                
                if (data.success) {
                    countBadge.textContent = data.online_count || 0;
                    statusText.textContent = 'En este momento';
                } else {
                    statusText.textContent = 'Sin conexión';
                    statusText.style.color = '#ef4444';
                }
            })
            .catch(err => console.error('Error fetching online users:', err));
    };

    fetchOnlineUsers();
    setInterval(fetchOnlineUsers, 30000); // 30s refresh
});
</script>
