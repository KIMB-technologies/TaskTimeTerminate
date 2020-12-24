//
//  TaskTimeTerminateWidget.swift
//  TaskTimeTerminateWidget
//
//  Copyright © 2020 KIMB-technologies. GPLv3
//

import WidgetKit
import SwiftUI
import Intents

struct Provider: TimelineProvider {
    // single preview
    func getSnapshot(in context: Context, completion: @escaping (SimpleEntry) -> Void) {
        completion(SimpleEntry(date: Date(), configuration: ConfigurationIntent()))
    }
    
    // multi preview
    func getTimeline(in context: Context, completion: @escaping (Timeline<SimpleEntry>) -> Void) {
        completion(Timeline(entries: [SimpleEntry(date: Date(), configuration: ConfigurationIntent())], policy: .atEnd))
    }
    
    // empty element
    func placeholder(in context: Context) -> SimpleEntry {
        SimpleEntry(date: Date(), configuration: ConfigurationIntent())
    }
}

// the content
struct SimpleEntry: TimelineEntry {
    let date: Date
    let configuration: ConfigurationIntent
}

// the real view (showing the content from file!!)
struct TaskTimeTerminateWidgetEntryView : View {
    var entry: Provider.Entry

    var body: some View {
        theMainView()
    }
    
    func theMainView() -> AnyView {
        let filepath = NSHomeDirectory() + "/TaskTimeTerminateWidget.txt"
        var content = ""
        do {
            let path = URL(fileURLWithPath: filepath);
            content = try String(contentsOf: path, encoding: .utf8)
        } catch {
            content = "Error unable to load stats!"
        }
        return AnyView(Text(content).font(.system(.body, design: .monospaced)))
    }
}

@main
struct TaskTimeTerminateWidget: Widget {
    var body: some WidgetConfiguration {
        StaticConfiguration(
            kind: "TaskTimeTerminateWidget",
            provider: Provider()
        ) { entry in
            TaskTimeTerminateWidgetEntryView(entry: entry)
        }
        .configurationDisplayName("TTT Widget")
        .description("This widget shows the current tasks and daily stats from TTT.")
        .supportedFamilies([.systemMedium, .systemLarge])
    }
}

struct TaskTimeTerminateWidget_Previews: PreviewProvider {
    static var previews: some View {
        TaskTimeTerminateWidgetEntryView(entry: SimpleEntry(date: Date(), configuration: ConfigurationIntent()))
            .previewContext(WidgetPreviewContext(family: .systemSmall))
    }
}
