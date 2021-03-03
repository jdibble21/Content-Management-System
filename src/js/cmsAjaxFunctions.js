function addWordToBlacklist(word){
    $.ajax({
        type: "POST",
        async: false,
        data: {
            word: word
        },
        url: "/php/contentManagementFilter/blacklistManager.php",
        success: async function () {
            await successFunction("Word Added");
        }
    });
}

function addWordToWhitelist(word){
    $.ajax({
        type: "POST",
        async: false,
        data: {
            word: word
        },
        url: "/php/contentManagementFilter/whitelistManager.php",
        success: async function () {
            $.ajax({
                url:"/php/contentManagementFilter/varStorage/whitelistWordAddStorage.php",
                cache:false,
                success:function(data){

                    if(data == "0"){
                        successFunction("Word Added");
                    }else{
                        errorFunction("Word already exists!",'');
                    }
                }
            })
        }
    });
}