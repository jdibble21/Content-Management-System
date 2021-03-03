if(!alertify.myAlert){
    //define a new dialog
    alertify.dialog('myAlert',function(){
        return{
            main:function(message){
                this.message = message;
            },
            setup:function(){
                return {
                    focus: { element:0 },
                    resizable: true,
                    movable: true,
                    maximized: true
                };
            },
            prepare:function(){
                this.setContent(this.message);
            },
            hooks: {
                onshow: function () {
                    this.elements.dialog.style.maxWidth = 'none';
                    this.elements.dialog.style.maxHeight = 'none';
                    this.elements.dialog.style.width = '80%';
                    this.elements.dialog.style.height = '80%';
                    this.elements.dialog.style.backgroundColor = 'rgba(0,0,0,0)';
                    this.elements.footer.style.display = 'none';
                    this.elements.header.style.display = 'none';
                }
            }
        }});
}