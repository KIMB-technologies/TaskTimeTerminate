//
//  AppDelegate.swift
//  TaskTimeTerminate
//
//  Created by KIMB on 22.03.20.
//  Copyright © 2020 KIMB-technologies. GPLv3
//

import Cocoa

@NSApplicationMain
class AppDelegate: NSObject, NSApplicationDelegate {

    func applicationDidFinishLaunching(_ aNotification: Notification) {
    }

    func applicationWillTerminate(_ aNotification: Notification) {
    }
    
    private func tapplicationShouldTerminateAfterLastWindowClosed(_ sender: NSApplication) -> Bool {
        return true;
    }
    
    func windowShouldClose(_ sender: Any) {
        NSApplication.shared.terminate(self)
    }
}
