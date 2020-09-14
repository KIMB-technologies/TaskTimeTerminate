//
//  ViewController.swift
//  TaskTimeTerminate
//
//  Created by KIMB on 22.03.20.
//  Copyright © 2020 KIMB-technologies. GPLv3
//

import Cocoa

extension FileHandle : TextOutputStream {
    public func write(_ string: String) {
        self.write(string.data(using: .utf8) ?? Data());
    }
}

class ViewController: NSViewController {
    
    /**
     * GUI References
     */
    @IBOutlet weak var categoryDropdown: NSPopUpButtonCell!
    
    @IBOutlet weak var nameInput: NSTextField!
    
    @IBOutlet weak var timeInput: NSTextField!
    
    /**
     * Enter Handling on Textareas
     */
    @IBAction func taskFieldEnter(_ sender: NSTextField) {
        if( timeInput.stringValue != "" && nameInput.stringValue != "" ){
            startClicked(nil);
        }
    }
    @IBAction func timeFieldEnter(_ sender: NSTextField) {
        if( timeInput.stringValue != "" ){
            startClicked(nil);
        }
    }
    
    
    /**
     * Send to PHP
     */
    @IBAction func startClicked(_ sender: NSButton?) {
        var output = "{ \"pause\" : false, ";
        output += " \"cat\": \"" + (categoryDropdown.titleOfSelectedItem ?? "").replacingOccurrences(of: "\"", with: "") + "\", ";
        output += " \"name\": \"" + nameInput.stringValue.replacingOccurrences(of: "\"", with: "") + "\", ";
        output += " \"time\": \"" + timeInput.stringValue.replacingOccurrences(of: "\"", with: "") + "\" ";
        output += "}";
        
        var stdOut = FileHandle.standardOutput;
        print(output, to:&stdOut);
        
        NSApplication.shared.terminate(self);
    }
    
    @IBAction func pauseClicked(_ sender: NSButton?) {
        var stdOut = FileHandle.standardOutput;
        print("{ \"pause\" : true, \"cat\": \"\", \"name\": \"\", \"time\": \"\" }", to:&stdOut);
        
        NSApplication.shared.terminate(self);
    }
    
    /**
     * On load setup, load args
     */
    override func viewDidLoad() {
        super.viewDidLoad();
        
        var catList = ["No", "Categories", "given!", "Use", "argument", "-cats Test,TTT,UUU"];
        var lastCat = "No";
        var lastTask = "Coding";
        
        if(CommandLine.arguments.count >= 3 ){
            for (key, value) in CommandLine.arguments.enumerated() {
                switch value {
                case "-cats":
                    catList = CommandLine.arguments[key+1].components(separatedBy: ",");
                    break;
                case "-lastcat":
                    lastCat = CommandLine.arguments[key+1];
                    break;
                case "-lasttask":
                    lastTask = CommandLine.arguments[key+1];
                    break;
                default:
                    break;
                }
            }
        }
        
        categoryDropdown.removeAllItems();
        categoryDropdown.addItems(withTitles: catList);
        categoryDropdown.selectItem(withTitle: lastCat);
        
        nameInput.placeholderString = lastTask;
        
    }
    
    /**
     * Stay in foreground
     */
    override func viewWillAppear() {
        NSApplication.shared.activate(ignoringOtherApps: true);
        view.window?.level = .floating;
        
        view.window!.standardWindowButton(NSWindow.ButtonType.closeButton)!.isHidden = true;
        view.window!.standardWindowButton(NSWindow.ButtonType.miniaturizeButton)!.isHidden = true;
        view.window!.standardWindowButton(NSWindow.ButtonType.zoomButton)!.isHidden = true;
    }

    override var representedObject: Any? {
        didSet {}
    }
}
