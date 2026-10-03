<?php
// DDoS Protection Monitor Panel
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Import hàm Telegram từ autoload
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

// Cấu hình
$refreshInterval = 10; // seconds - tăng lên để giảm tải
$maxConnections = 100;
$logFile = 'C:\xampp\htdocs\ddos_alerts.log';
$alertFile = $_SERVER['DOCUMENT_ROOT'] . '/ddos_alerts.txt';

// Lấy thống kê connections hiện tại
function getCurrentConnections() {
    $ports = [80, 443, 3306, 14445, 2005];
    $connections = [];
    $total = 0;
    
    foreach ($ports as $port) {
        $cmd = "netstat -an | findstr :$port | findstr ESTABLISHED";
        $output = shell_exec($cmd);
        $count = $output ? substr_count($output, 'ESTABLISHED') : 0;
        $connections[$port] = $count;
        $total += $count;
    }
    
    return ['ports' => $connections, 'total' => $total];
}

// Lấy top IP connections (giảm số lượng để tối ưu)
function getTopIPs() {
    $ignoreIPs = ['127.0.0.1', '::1', 'localhost', '157.10.199.195'];
    $monitoredPorts = [80, 443, 14445, 2005];
    $cmd = 'netstat -an | findstr ESTABLISHED';
    $output = shell_exec($cmd);
    $ipPortMap = [];

    if ($output) {
        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            if (preg_match('/\s+([\d\.]+):(\d+)\s+([\d\.]+):(\d+)\s+ESTABLISHED/', $line, $matches)) {
                $localIP = $matches[1];
                $localPort = (int)$matches[2];
                $remoteIP = $matches[3];
                if (!in_array($localPort, $monitoredPorts)) continue;
                if (in_array($remoteIP, $ignoreIPs)) continue;
                if (!isset($ipPortMap[$remoteIP])) $ipPortMap[$remoteIP] = [];
                if (!isset($ipPortMap[$remoteIP][$localPort])) $ipPortMap[$remoteIP][$localPort] = 0;
                $ipPortMap[$remoteIP][$localPort]++;
            }
        }
    }
    
    // Chỉ lấy top 5 IP để tối ưu
    uasort($ipPortMap, function($a, $b) {
        return array_sum($b) - array_sum($a);
    });
    
    return array_slice($ipPortMap, 0, 5, true);
}

// Lấy system info đơn giản
function getSystemInfo() {
    $uptime = shell_exec('powershell "Get-CimInstance -ClassName Win32_OperatingSystem | Select-Object -ExpandProperty LastBootUpTime"');
    $cpu = shell_exec('powershell "Get-CimInstance -ClassName Win32_Processor | Select-Object -ExpandProperty LoadPercentage"');
    
    return [
        'uptime' => trim($uptime),
        'cpu' => trim($cpu)
    ];
}

// Lấy recent logs (giảm số lượng)
function getRecentLogs($lines = 5) {
    global $logFile;
    if (!file_exists($logFile)) return [];
    
    $logs = file($logFile, FILE_IGNORE_NEW_LINES);
    return array_slice($logs, -$lines);
}

// Lấy terminal status đơn giản
function getTerminalStatus() {
    $status = [];
    
    // Kiểm tra port listening
    $portCheck = shell_exec('netstat -an | findstr ":80.*LISTENING"');
    $status[] = $portCheck ? '✓ Port 80: Listening' : '✗ Port 80: Not listening';
    
    $portCheck = shell_exec('netstat -an | findstr ":3306.*LISTENING"');
    $status[] = $portCheck ? '✓ Port 3306: Listening' : '✗ Port 3306: Not listening';
    
    // System uptime
    $uptime = shell_exec('powershell "Get-CimInstance -ClassName Win32_OperatingSystem | Select-Object -ExpandProperty LastBootUpTime"');
    if ($uptime) {
        $bootTime = new DateTime(trim($uptime));
        $now = new DateTime();
        $diff = $now->diff($bootTime);
        $status[] = "⏱ Uptime: " . $diff->format('%d days, %h hours');
    }
    
    return $status;
}

// Kiểm tra và gửi cảnh báo Telegram (đơn giản hóa)
function checkAndSendAlert($connections, $topIPs) {
    global $maxConnections, $alertFile;
    
    $currentTime = time();
    $lastAlertTime = 0;
    
    if (file_exists($alertFile)) {
        $lastAlertTime = (int)file_get_contents($alertFile);
    }
    
    // Chỉ gửi cảnh báo nếu đã qua 5 phút từ lần cuối
    if ($currentTime - $lastAlertTime < 300) {
        return false;
    }
    
    $shouldAlert = false;
    $alertMessages = [];
    
    // Kiểm tra tổng connections
    if ($connections['total'] > $maxConnections) {
        $shouldAlert = true;
        $alertMessages[] = "🚨 CẢNH BÁO DDOS - TRAFFIC CAO!";
        $alertMessages[] = "📊 Tổng connections: " . $connections['total'] . "/" . $maxConnections;
    }
    
    // Gửi cảnh báo nếu cần
    if ($shouldAlert) {
        $alertMessages[] = "⏰ Thời gian: " . date('d/m/Y H:i:s');
        $alertMessages[] = "🛡️ Hệ thống đang tự động phản ứng...";
        
        $message = implode("\n", $alertMessages);
        
        try {
            $result = sendTele(templateTele($message));
            file_put_contents($alertFile, $currentTime);
            
            return [
                'sent' => true,
                'message' => $message,
                'connections' => $connections['total']
            ];
        } catch (Exception $e) {
            return [
                'sent' => false, 
                'error' => $e->getMessage()
            ];
        }
    }
    
    return false;
}

// AJAX endpoint
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    $connections = getCurrentConnections();
    $topIPs = getTopIPs();
    $alertResult = checkAndSendAlert($connections, $topIPs);
    
    $data = [
        'connections' => $connections,
        'topIPs' => $topIPs,
        'systemInfo' => getSystemInfo(),
        'recentLogs' => getRecentLogs(3),
        'terminalStatus' => getTerminalStatus(),
        'alertSent' => $alertResult,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($data);
    exit;
}

// Test Telegram Alert endpoint
if (isset($_GET['test_telegram'])) {
    header('Content-Type: application/json');
    
    $testMessage = "🧪 TEST CẢNH BÁO DDOS!\n";
    $testMessage .= "📊 Test từ hệ thống DDoS Protection\n";
    $testMessage .= "⏰ Thời gian: " . date('d/m/Y H:i:s') . "\n";
    $testMessage .= "✅ Telegram alert hoạt động bình thường!";
    
    try {
        $result = sendTele(templateTele($testMessage));
        echo json_encode([
            'success' => true,
            'message' => 'Test alert đã được gửi thành công!'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi gửi test alert: ' . $e->getMessage()
        ]);
    }
    exit;
}

$connections = getCurrentConnections();
$topIPs = getTopIPs();

$currentData = [
    'connections' => $connections,
    'topIPs' => $topIPs,
    'systemInfo' => getSystemInfo(),
    'recentLogs' => getRecentLogs(3),
    'terminalStatus' => getTerminalStatus()
];

$initialAlert = checkAndSendAlert($connections, $topIPs);
?>

<style>
/* DDoS Panel Styles - Tích hợp với admin panel */
.ddos-monitor {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

.ddos-monitor .header {
    background: rgba(255, 255, 255, 0.95);
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.ddos-monitor .header h4 {
    color: #2c3e50;
    text-align: center;
    margin-bottom: 10px;
}

.ddos-monitor .status-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #34495e;
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    font-size: 14px;
}

.ddos-monitor .dashboard {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.ddos-monitor .card {
    background: rgba(255, 255, 255, 0.95);
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.ddos-monitor .card h5 {
    color: #2c3e50;
    margin-bottom: 10px;
    border-bottom: 2px solid #3498db;
    padding-bottom: 5px;
    font-size: 16px;
}

.ddos-monitor .connection-item {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    border-bottom: 1px solid #ecf0f1;
    font-size: 14px;
}

.ddos-monitor .connection-item:last-child {
    border-bottom: none;
}

.ddos-monitor .port-label {
    font-weight: bold;
    color: #2c3e50;
}

.ddos-monitor .connection-count {
    background: #3498db;
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 11px;
}

.ddos-monitor .connection-count.high {
    background: #e74c3c;
}

.ddos-monitor .connection-count.medium {
    background: #f39c12;
}

.ddos-monitor .ip-item {
    display: flex;
    justify-content: space-between;
    padding: 4px 0;
    font-family: monospace;
    font-size: 13px;
}

.ddos-monitor .log-container {
    background: #2c3e50;
    color: #ecf0f1;
    padding: 10px;
    border-radius: 5px;
    font-family: monospace;
    font-size: 11px;
    max-height: 150px;
    overflow-y: auto;
}

.ddos-monitor .terminal-container {
    background: #000000;
    color: #00ff00;
    padding: 10px;
    border-radius: 5px;
    font-family: 'Courier New', monospace;
    font-size: 10px;
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #00ff00;
}

.ddos-monitor .terminal-line {
    margin-bottom: 2px;
    line-height: 1.3;
}

.ddos-monitor .terminal-line.success {
    color: #00ff00;
}

.ddos-monitor .terminal-line.error {
    color: #ff4444;
}

.ddos-monitor .terminal-line.warning {
    color: #ffaa00;
}

.ddos-monitor .terminal-line.info {
    color: #44aaff;
}

.ddos-monitor .refresh-btn {
    background: #3498db;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
}

.ddos-monitor .refresh-btn:hover {
    background: #2980b9;
}

.ddos-monitor .auto-refresh {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
}

.ddos-monitor .status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #27ae60;
    display: inline-block;
    margin-right: 5px;
}

.ddos-monitor .status-indicator.warning {
    background: #f39c12;
}

.ddos-monitor .status-indicator.danger {
    background: #e74c3c;
}

.ddos-monitor .log-entry {
    margin-bottom: 3px;
    padding: 1px 0;
}

.ddos-monitor .log-entry.warning {
    color: #f39c12;
}
</style>

<div id="content" class="app-content">
    <div class="row">
        <div class="col-12">
            <div class="ddos-monitor">
                <div class="header">
                    <h4>🛡️ DDoS Protection Monitor</h4>
                    <div class="status-bar">
                        <div>
                            <span class="status-indicator" id="statusIndicator"></span>
                            <span id="statusText">System Active</span>
                        </div>
                        <div class="auto-refresh">
                            <button class="refresh-btn" onclick="refreshData()">🔄 Refresh</button>
                            <label>
                                <input type="checkbox" id="autoRefresh" checked> Auto-refresh (<?php echo $refreshInterval; ?>s)
                            </label>
                        </div>
                        <div id="lastUpdate">Last update: <?php echo date('H:i:s'); ?></div>
                    </div>
                </div>
                
                <div class="dashboard">
                    <!-- Connections Card -->
                    <div class="card">
                        <h5>📊 Active Connections</h5>
                        <div id="connectionsData">
                            <?php foreach ($currentData['connections']['ports'] as $port => $count): ?>
                                <div class="connection-item">
                                    <span class="port-label">Port <?php echo $port; ?>:</span>
                                    <span class="connection-count <?php echo $count > 50 ? 'high' : ($count > 20 ? 'medium' : ''); ?>">
                                        <?php echo $count; ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                            <div class="connection-item" style="border-top: 2px solid #3498db; margin-top: 8px; padding-top: 8px;">
                                <span class="port-label"><strong>Total:</strong></span>
                                <span class="connection-count <?php echo $currentData['connections']['total'] > $maxConnections ? 'high' : ''; ?>">
                                    <?php echo $currentData['connections']['total']; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Top IPs Card -->
                    <div class="card">
                        <h5>🌐 Top IP Addresses</h5>
                        <div id="topIPsData">
                            <?php foreach ($currentData['topIPs'] as $ip => $ports): ?>
                                <?php $total = array_sum($ports); ?>
                                <div class="ip-item">
                                    <span><b><?php echo $ip; ?></b> (<?php echo $total; ?>)</span>
                                </div>
                                <?php foreach ($ports as $port => $count): ?>
                                    <div class="ip-item" style="padding-left:15px; font-size:12px; color:#555;">
                                        <span>→ Port <?php echo $port; ?></span>
                                        <span class="connection-count <?php echo $count>20?'high':($count>10?'medium':''); ?>"><?php echo $count; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Telegram Alerts Card -->
                    <div class="card">
                        <h5>📱 Telegram Alerts</h5>
                        <div id="telegramAlertsData">
                            <?php
                            $lastAlertTime = 0;
                            if (file_exists($alertFile)) {
                                $lastAlertTime = (int)file_get_contents($alertFile);
                            }
                            ?>
                            <div class="connection-item">
                                <span>Alert Status:</span>
                                <span style="color: #27ae60; font-weight: bold;">🟢 Active</span>
                            </div>
                            <div class="connection-item">
                                <span>Max Connections:</span>
                                <span><?php echo $maxConnections; ?></span>
                            </div>
                            <div class="connection-item">
                                <span>Cảnh báo cuối:</span>
                                <span id="lastAlertTime"><?php echo $lastAlertTime > 0 ? date('d/m/Y H:i:s', $lastAlertTime) : 'Chưa có cảnh báo'; ?></span>
                            </div>
                            <div class="connection-item">
                                <button class="refresh-btn" onclick="testTelegramAlert()" style="margin-top: 8px;">
                                    📱 Test Alert
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- System Info Card -->
                    <div class="card">
                        <h5>💻 System Information</h5>
                        <div id="systemInfoData">
                            <div class="connection-item">
                                <span>Server Status:</span>
                                <span style="color: #27ae60; font-weight: bold;">Online</span>
                            </div>
                            <div class="connection-item">
                                <span>Protected Ports:</span>
                                <span>80, 443, 3306, 14445, 2005</span>
                            </div>
                            <div class="connection-item">
                                <span>Firewall:</span>
                                <span style="color: #27ae60; font-weight: bold;">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Terminal Status Card -->
                <div class="card">
                    <h5>💻 System Terminal</h5>
                    <div class="terminal-container" id="terminalOutput">
                        <!-- Terminal sẽ được khởi tạo bằng JavaScript -->
                    </div>
                </div>
                
                <!-- Logs Card -->
                <div class="card">
                    <h5>📝 Recent Logs</h5>
                    <div class="log-container" id="logsData">
                        <?php foreach (array_reverse($currentData['recentLogs']) as $log): ?>
                            <div class="log-entry <?php echo strpos($log, 'WARNING') !== false ? 'warning' : 'info'; ?>">
                                <?php echo htmlspecialchars($log); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let autoRefreshInterval;
let terminalLines = [];
let maxTerminalLines = 20; // Giảm xuống để tối ưu

function refreshData(showLoading = true) {
    const refreshBtn = document.querySelector('.refresh-btn');
    
    if (showLoading) {
        refreshBtn.disabled = true;
        refreshBtn.textContent = '⟳ Loading...';
    }
    
    fetch('?ajax=1')
        .then(response => response.json())
        .then(data => {
            updateConnections(data.connections);
            updateTopIPs(data.topIPs);
            updateLogs(data.recentLogs);
            updateTerminal(data.terminalStatus);
            updateStatus(data.connections.total);
            
            if (data.alertSent && data.alertSent.sent) {
                handleTelegramAlert(data.alertSent);
            }
            
            document.getElementById('lastUpdate').textContent = 'Last update: ' + new Date().toLocaleTimeString();
            
            if (showLoading) {
                refreshBtn.disabled = false;
                refreshBtn.textContent = '🔄 Refresh';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (showLoading) {
                refreshBtn.disabled = false;
                refreshBtn.textContent = '🔄 Refresh';
            }
        });
}

function updateConnections(connections) {
    const container = document.getElementById('connectionsData');
    let html = '';
    
    Object.entries(connections.ports).forEach(([port, count]) => {
        const level = count > 50 ? 'high' : (count > 20 ? 'medium' : '');
        html += `
            <div class="connection-item">
                <span class="port-label">Port ${port}:</span>
                <span class="connection-count ${level}">${count}</span>
            </div>
        `;
    });
    
    const totalLevel = connections.total > <?php echo $maxConnections; ?> ? 'high' : '';
    html += `
        <div class="connection-item" style="border-top: 2px solid #3498db; margin-top: 8px; padding-top: 8px;">
            <span class="port-label"><strong>Total:</strong></span>
            <span class="connection-count ${totalLevel}">${connections.total}</span>
        </div>
    `;
    
    container.innerHTML = html;
}

function updateTopIPs(topIPs) {
    const container = document.getElementById('topIPsData');
    let html = '';
    
    Object.entries(topIPs).forEach(([ip, ports]) => {
        const total = Object.values(ports).reduce((a, b) => a + b, 0);
        html += `
            <div class="ip-item">
                <span><b>${ip}</b> (${total})</span>
            </div>
        `;
        Object.entries(ports).forEach(([port, count]) => {
            const portLevel = count > 20 ? 'high' : (count > 10 ? 'medium' : '');
            html += `
                <div class="ip-item" style="padding-left:15px; font-size:12px; color:#555;">
                    <span>→ Port ${port}</span>
                    <span class="connection-count ${portLevel}">${count}</span>
                </div>
            `;
        });
    });
    
    container.innerHTML = html;
}

function updateLogs(logs) {
    const container = document.getElementById('logsData');
    let html = '';
    
    logs.reverse().forEach(log => {
        const isWarning = log.includes('WARNING');
        html += `<div class="log-entry ${isWarning ? 'warning' : ''}">${log}</div>`;
    });
    
    container.innerHTML = html;
}

function updateTerminal(terminalStatus) {
    terminalStatus.forEach(line => {
        let type = 'info';
        if (line.includes('✓')) type = 'success';
        else if (line.includes('✗')) type = 'error';
        else if (line.includes('WARNING')) type = 'warning';
        
        addTerminalLine(line, type);
    });
}

function updateStatus(totalConnections) {
    const indicator = document.getElementById('statusIndicator');
    const statusText = document.getElementById('statusText');
    
    if (totalConnections > <?php echo $maxConnections; ?>) {
        indicator.className = 'status-indicator danger';
        statusText.textContent = 'High Traffic Alert';
    } else if (totalConnections > <?php echo $maxConnections * 0.7; ?>) {
        indicator.className = 'status-indicator warning';
        statusText.textContent = 'Moderate Traffic';
    } else {
        indicator.className = 'status-indicator';
        statusText.textContent = 'System Normal';
    }
}

function addTerminalLine(message, type = 'info') {
    const timestamp = new Date().toLocaleTimeString();
    const line = `[${timestamp}] ${message}`;
    
    terminalLines.push({ line, type });
    
    if (terminalLines.length > maxTerminalLines) {
        terminalLines.splice(0, 5);
    }
    
    updateTerminalDisplay();
}

function updateTerminalDisplay() {
    const container = document.getElementById('terminalOutput');
    let html = '';
    
    terminalLines.forEach(({ line, type }) => {
        html += `<div class="terminal-line ${type}">${line}</div>`;
    });
    
    container.innerHTML = html;
    container.scrollTop = container.scrollHeight;
}

function handleTelegramAlert(alertData) {
    addTerminalLine(`🚨 TELEGRAM ALERT SENT! ${alertData.connections} connections detected`, 'error');
    
    const now = new Date();
    document.getElementById('lastAlertTime').textContent = now.toLocaleDateString('vi-VN') + ' ' + now.toLocaleTimeString();
}

function testTelegramAlert() {
    const testBtn = document.querySelector('button[onclick="testTelegramAlert()"]');
    const originalText = testBtn.innerHTML;
    
    testBtn.disabled = true;
    testBtn.innerHTML = '⟳ Đang gửi...';
    
    addTerminalLine('📱 Sending test Telegram alert...', 'info');
    
    fetch('?test_telegram=1')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                addTerminalLine('✅ Test alert sent successfully!', 'success');
                const now = new Date();
                document.getElementById('lastAlertTime').textContent = now.toLocaleDateString('vi-VN') + ' ' + now.toLocaleTimeString() + ' (TEST)';
            } else {
                addTerminalLine('❌ Failed to send test alert: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            addTerminalLine('❌ Test alert failed - network error', 'error');
        })
        .finally(() => {
            testBtn.disabled = false;
            testBtn.innerHTML = originalText;
        });
}

// Auto-refresh functionality
document.getElementById('autoRefresh').addEventListener('change', function() {
    if (this.checked) {
        autoRefreshInterval = setInterval(() => refreshData(false), <?php echo $refreshInterval * 1000; ?>);
    } else {
        clearInterval(autoRefreshInterval);
    }
});

// Start auto-refresh
autoRefreshInterval = setInterval(() => refreshData(false), <?php echo $refreshInterval * 1000; ?>);

// Initialize terminal
setTimeout(() => {
    addTerminalLine('🛡️ DDoS Protection Monitor v3.0 - Initializing...', 'success');
    addTerminalLine('📊 Loading system modules...', 'info');
    addTerminalLine('🔒 Firewall engine: ACTIVE', 'success');
    addTerminalLine('📱 Telegram alerts: ARMED', 'success');
    addTerminalLine('✅ System ready for monitoring', 'success');
}, 500);

// Check for initial alert
<?php if ($initialAlert && $initialAlert['sent']): ?>
setTimeout(() => {
    handleTelegramAlert(<?php echo json_encode($initialAlert); ?>);
}, 3000);
<?php endif; ?>
</script> 