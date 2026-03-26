@echo off
start msedge.exe --app="http://teamhub.atwebpages.com/ui/dashboard.php"
if %errorlevel% neq 0 (
    start chrome.exe --app="http://teamhub.atwebpages.com/ui/dashboard.php"
    if %errorlevel% neq 0 (
        start "" "http://teamhub.atwebpages.com/ui/dashboard.php"
    )
)
