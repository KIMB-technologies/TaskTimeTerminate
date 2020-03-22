//
//  ViewController.swift
//  TaskTimeTerminate
//
//  Created by KIMB on 22.03.20.
//  Copyright © 2020 KIMB-technologies. GPLv3
//

import Cocoa

class ViewController: NSViewController {
    
    @IBOutlet weak var categoryDropdown: NSPopUpButtonCell!
    
    @IBOutlet weak var nameInput: NSTextField!
    
    @IBOutlet weak var timeInput: NSTextField!
    
    
    @IBAction func startClicked(_ sender: NSButton) {
        var output = "{ pause : false, ";
        output += " cat: \"" + (categoryDropdown.titleOfSelectedItem ?? "").replacingOccurrences(of: "\"", with: "") + "\", ";
        output += " name: \"" + nameInput.stringValue.replacingOccurrences(of: "\"", with: "") + "\", ";
        output += " time: \"" + timeInput.stringValue.replacingOccurrences(of: "\"", with: "") + "\" ";
        output += "}";
        
        print(output);
        
        NSApplication.shared.terminate(self);
    }
    
    @IBAction func pauseClicked(_ sender: NSButton) {
        print("{ pause : true, cat: \"\", name: \"\", time: \"\" }");
        NSApplication.shared.terminate(self);
    }
    
    override func viewDidLoad() {
        super.viewDidLoad()
        
        var catList = ["No", "Categories", "given!", "Use", "argument", "-cats Test,TTT,UUU"];
        if(CommandLine.arguments.count == 3 && CommandLine.arguments[1] == "-cats" ){
            catList = CommandLine.arguments[2].components(separatedBy: ",");
        }
        
        categoryDropdown.removeAllItems();
        categoryDropdown.addItems(withTitles: catList);
    }

    override var representedObject: Any? {
        didSet {}
    }
}
