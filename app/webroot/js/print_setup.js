function getPrinterName(printerName){
    var printerList = jsPrintSetup.getPrintersList().split(",");
    var getPrinter  = '';
    $.each(printerList, function( index, printer ) {
        if(printer.search(printerName) != -1){
            getPrinter = printer;
        }
    });
    return getPrinter;
}
