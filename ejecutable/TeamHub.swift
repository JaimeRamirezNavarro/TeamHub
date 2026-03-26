import Cocoa
import WebKit

class AppDelegate: NSObject, NSApplicationDelegate, WKNavigationDelegate {
    var window: NSWindow!
    var webView: WKWebView!
    var statusItem: NSStatusItem!
    
    func applicationDidFinishLaunching(_ aNotification: Notification) {
        // 1. Create Window
        let rect = NSRect(x: 0, y: 0, width: 1200, height: 800)
        window = NSWindow(contentRect: rect,
                          styleMask: [.titled, .closable, .miniaturizable, .resizable],
                          backing: .buffered, defer: false)
        window.center()
        window.title = "TeamHub"
        window.isReleasedWhenClosed = false
        
        // 2. Configure WebView
        let config = WKWebViewConfiguration()
        
        // Prevent aggressive caching
        let store = WKWebsiteDataStore.nonPersistent()
        config.websiteDataStore = store
        
        // Ensure JavaScript is fully enabled
        let preferences = WKPreferences()
        preferences.javaScriptCanOpenWindowsAutomatically = true
        config.preferences = preferences
        
        let pagePrefs = WKWebpagePreferences()
        pagePrefs.allowsContentJavaScript = true
        config.defaultWebpagePreferences = pagePrefs
        
        webView = WKWebView(frame: rect, configuration: config)
        webView.navigationDelegate = self
        webView.autoresizingMask = [.width, .height]
        
        // Fix for CORS / HTTP constraints in modern WKWebView
        webView.customUserAgent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15 TeamHubNative/1.0"
        
        window.contentView?.addSubview(webView)
        
        // 3. Load URL
        if let url = URL(string: "http://teamhub.atwebpages.com/") {
            let request = URLRequest(url: url, cachePolicy: .reloadIgnoringLocalAndRemoteCacheData, timeoutInterval: 30)
            webView.load(request)
        }
        
        window.makeKeyAndOrderFront(nil)
        
        // 4. Setup Status Item (Menu Bar)
        statusItem = NSStatusBar.system.statusItem(withLength: NSStatusItem.variableLength)
        if let button = statusItem.button {
            button.title = "TH"
            button.font = NSFont.boldSystemFont(ofSize: 14)
        }
        
        let menu = NSMenu()
        menu.addItem(NSMenuItem(title: "Abrir TeamHub", action: #selector(showWindow), keyEquivalent: "o"))
        menu.addItem(NSMenuItem.separator())
        menu.addItem(NSMenuItem(title: "Salir", action: #selector(quitApp), keyEquivalent: "q"))
        statusItem.menu = menu
        
        // Intercept close button
        NotificationCenter.default.addObserver(self, selector: #selector(windowWillClose), name: NSWindow.willCloseNotification, object: window)
    }
    
    @objc func showWindow() {
        window.makeKeyAndOrderFront(nil)
        NSApp.activate(ignoringOtherApps: true)
    }
    
    @objc func quitApp() {
        NSApplication.shared.terminate(self)
    }
    
    @objc func windowWillClose(_ notification: Notification) {
        // Window closed but app stays in background
    }
}

let app = NSApplication.shared
let delegate = AppDelegate()
app.delegate = delegate
app.run()
