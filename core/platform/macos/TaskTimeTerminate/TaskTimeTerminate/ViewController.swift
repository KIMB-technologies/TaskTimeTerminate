//
//  ViewController.swift
//  TaskTimeTerminate
//
//  Created by KIMB on 22.03.20.
//  Copyright © 2020 KIMB-technologies. GPLv3
//

import Cocoa
import Socket

extension FileHandle : TextOutputStream {
    public func write(_ string: String) {
        self.write(string.data(using: .utf8) ?? Data());
    }
}

class ViewController: NSViewController, NSComboBoxCellDataSource {
    
    /**
     * GUI References
     */
    @IBOutlet weak var categoryDropdown: NSPopUpButtonCell!
    
    @IBOutlet weak var timeInput: NSTextField!
    
    @IBOutlet weak var taskComboBox: NSComboBoxCell!
    
    var currentCompletions : [String]!
    var maxCompletions : Int!
    
    /**
     * Enter Handling on Textareas
     */
    @IBAction func nameFieldEnter(_ sender: Any) {
        if( timeInput.stringValue != "" && taskComboBox.stringValue != "" ){
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
        output += " \"name\": \"" + taskComboBox.stringValue.replacingOccurrences(of: "\"", with: "") + "\", ";
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
     * Autocomplete
     */
    func getCompletions(prefix : String) -> [String] {
        let msg = prefix + "\n";
        
        let socket = try? Socket.create(family: .unix, type: .stream, proto: .tcp);
        try? socket?.connect( to: "/private/tmp/TaskTimeTerminateAutocomplete.sock" );
        try? socket?.setBlocking(mode: true);
        try? socket?.write(from: msg);
        let reponse = try? socket?.readString();
        socket?.close();
        return reponse?.trimmingCharacters(in: .whitespacesAndNewlines).components(separatedBy: ",") ?? []
    }
    
    func updateCurrentCompletions(){
        if( !taskComboBox.isAccessibilityExpanded() ){
            taskComboBox.setAccessibilityExpanded(true);
        }
        let completions = getCompletions(prefix: taskComboBox.stringValue);
        for index in 0...(maxCompletions-1){
            if( index < maxCompletions && index < completions.count  ){
                currentCompletions[index] = completions[index];
            }
            else{
                currentCompletions[index] = "";
            }
        }
        taskComboBox.reloadData();
      }
      
      func comboBoxCell(_ : NSComboBoxCell, completedString: String) -> String? {
          return "";
      }
     
      func comboBoxCell(_ : NSComboBoxCell, indexOfItemWithStringValue: String) -> Int {
          updateCurrentCompletions();
          return NSNotFound;
      }
      
      func comboBoxCell(_ : NSComboBoxCell, objectValueForItemAt: Int) -> Any {
          updateCurrentCompletions();
          return currentCompletions[objectValueForItemAt];
      }
    
      func numberOfItems(in: NSComboBoxCell) -> Int {
          return maxCompletions;
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
                
        maxCompletions = 10;
        currentCompletions = [String](repeating: "", count: maxCompletions);
        taskComboBox.placeholderString = lastTask;
        taskComboBox.dataSource = self;
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
