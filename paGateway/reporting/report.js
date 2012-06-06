$(document).ready(function(){
	
	$('#date-from').datepicker();
	$('#date-to').datepicker();
	
	$("#yourTableID").chromatable({
		
				width: "100%", // specify 100%, auto, or a fixed pixel amount
				height: "400px",
				scrolling: "no" // must have the jquery-1.3.2.min.js script installed to use
				
			});
			
		$("#mainTable").chromatable({
		
				width: "900px",
				height: "400px",
				scrolling: "yes"
				
			});	
	
	
	
});


/*
function UpdateTableHeaders() {
       $(".persist-area").each(function() {
       
           var el             = $(this),
               offset         = el.offset(),
               scrollTop      = $(window).scrollTop(),
               floatingHeader = $(".floatingHeader", this)
           
           if ((scrollTop > offset.top) && (scrollTop < offset.top + el.height())) {
               floatingHeader.css({
                "visibility": "visible"
               });
           } else {
               floatingHeader.css({
                "visibility": "hidden"
               });      
           };
       });
    }
    
    // DOM Ready      
    $(function() {
    
       var clonedHeaderRow;
    
       $(".persist-area").each(function() {
           clonedHeaderRow = $(".persist-header", this);
           clonedHeaderRow
             .before(clonedHeaderRow.clone())
             .css("width", clonedHeaderRow.width())
             .addClass("floatingHeader");
             
       });
       
       $(window)
        .scroll(UpdateTableHeaders)
        .trigger("scroll");
       
    });
    
    */
