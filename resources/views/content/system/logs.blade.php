@extends('layouts/contentNavbarLayout')

@section('title', 'Real-time System Logs')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card bg-dark text-white shadow-lg">
            <div class="card-header d-flex justify-content-between align-items-center border-bottom border-secondary">
                <h5 class="mb-0 text-white"><i class="bx bx-terminal me-2"></i> Real-time System Logs (laravel.log)</h5>
                <div class="d-flex gap-2">
                    <button id="toggleScroll" class="btn btn-sm btn-outline-info">
                        <i class="bx bx-mouse me-1"></i> Auto Scroll: ON
                    </button>
                    <form action="{{ route('logs.clear') }}" method="POST" onsubmit="return confirm('Clear all logs?')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bx bx-trash me-1"></i> Clear Logs
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="logContainer" style="height: 500px; overflow-y: auto; font-family: 'Courier New', Courier, monospace; font-size: 13px; line-height: 1.5; padding: 20px; background-color: #0d1117;">
                    <div id="logContent">
                        <div class="text-secondary">Initializing real-time stream...</div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center border-top border-secondary py-2">
                <div class="text-secondary small">
                    <span id="updateTimer">Last Update: --:--:--</span>
                </div>
                <div class="d-flex align-items-center">
                    <span class="spinner-grow spinner-grow-sm text-success me-2" role="status"></span>
                    <span class="text-success small fw-bold">LIVE</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #logContainer::-webkit-scrollbar {
        width: 8px;
    }
    #logContainer::-webkit-scrollbar-track {
        background: #0d1117;
    }
    #logContainer::-webkit-scrollbar-thumb {
        background: #30363d;
        border-radius: 4px;
    }
    #logContainer::-webkit-scrollbar-thumb:hover {
        background: #484f58;
    }
    .log-line {
        margin-bottom: 2px;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    .log-error { color: #ff7b72; }
    .log-info { color: #79c0ff; }
    .log-warning { color: #d29922; }
    .log-success { color: #7ee787; }
    .log-time { color: #8b949e; margin-right: 8px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const logContainer = document.getElementById('logContainer');
    const logContent = document.getElementById('logContent');
    const timer = document.getElementById('updateTimer');
    const toggleScrollBtn = document.getElementById('toggleScroll');
    
    let autoScroll = true;
    let lastLogContent = '';

    toggleScrollBtn.addEventListener('click', function() {
        autoScroll = !autoScroll;
        toggleScrollBtn.innerHTML = autoScroll ? '<i class="bx bx-mouse me-1"></i> Auto Scroll: ON' : '<i class="bx bx-mouse me-1"></i> Auto Scroll: OFF';
        toggleScrollBtn.classList.toggle('btn-outline-info');
        toggleScrollBtn.classList.toggle('btn-outline-secondary');
    });

    function formatLogs(rawLogs) {
        if (!rawLogs) return '<div class="text-secondary">No logs found.</div>';
        
        return rawLogs.split('\n').map(line => {
            if (!line.trim()) return '';
            
            let className = '';
            if (line.includes('.ERROR')) className = 'log-error';
            else if (line.includes('.INFO')) className = 'log-info';
            else if (line.includes('.WARNING')) className = 'log-warning';
            
            // Extract time if possible [2026-05-15 16:19:01]
            const timeMatch = line.match(/^\[(.*?)\]/);
            if (timeMatch) {
                const time = timeMatch[0];
                const rest = line.substring(time.length);
                return `<div class="log-line ${className}"><span class="log-time">${time}</span>${rest}</div>`;
            }
            
            return `<div class="log-line ${className}">${line}</div>`;
        }).join('');
    }

    function fetchLogs() {
        fetch('{{ route("logs.fetch") }}')
            .then(response => response.json())
            .then(data => {
                if (data.logs !== lastLogContent) {
                    logContent.innerHTML = formatLogs(data.logs);
                    lastLogContent = data.logs;
                    
                    if (autoScroll) {
                        logContainer.scrollTop = logContainer.scrollHeight;
                    }
                }
                timer.innerText = 'Last Update: ' + data.time;
            })
            .catch(err => {
                console.error('Error fetching logs:', err);
                timer.innerText = 'Error connection...';
            });
    }

    // Initial fetch
    fetchLogs();

    // Poll every 2 seconds
    setInterval(fetchLogs, 2000);
});
</script>
@endsection
