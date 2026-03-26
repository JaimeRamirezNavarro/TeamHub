const { app, BrowserWindow, Menu, Tray } = require('electron')
const path = require('path')

let tray = null
let win = null

function createWindow() {
    win = new BrowserWindow({
        width: 1200,
        height: 800,
        title: "TeamHub",
        webPreferences: {
            nodeIntegration: false,
            contextIsolation: true
        }
    })

    // Remove the default menu bar for an app-like feel
    Menu.setApplicationMenu(null)

    // Load the remote URL that the user provided
    win.loadURL('http://teamhub.atwebpages.com/ui/dashboard.php')

    // Prevent window from closing completely so it stays in the tray
    win.on('close', function (event) {
        if (!app.isQuiting) {
            event.preventDefault()
            win.hide()
        }
        return false
    })
}

// Ensure the app starts up fully
app.whenReady().then(() => {
    createWindow()

    // Create a system tray icon (using a default placeholder for now)
    // We'll use a blank native image as we don't have a specific icon file
    tray = new Tray(path.join(__dirname, 'iconPlaceholder')) // We will handle the missing image warning

    const contextMenu = Menu.buildFromTemplate([
        { label: 'Abrir TeamHub', click: () => { win.show() } },
        { type: 'separator' },
        {
            label: 'Salir', click: () => {
                app.isQuiting = true
                app.quit()
            }
        }
    ])
    tray.setToolTip('TeamHub')
    tray.setContextMenu(contextMenu)

    // Map double click to showing the window (Windows specific)
    tray.on('double-click', () => {
        win.show()
    })
})
