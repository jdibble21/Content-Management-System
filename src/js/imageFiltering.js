// Implements nsfw prediction model
function blurImages() {
    const imgArray = document.getElementsByClassName('blurBlockedImage')
    for (let i = 0; i < imgArray.length; i++) {
        const img = imgArray[i]
        img.crossOrigin = "anonymous"
        img.style.filter='blur(30px)';
    }
}
function unBlurImages() {
    const imgArray = document.getElementsByClassName('filter')
    for (let i = 0; i < imgArray.length; i++) {
        const img = imgArray[i]
        img.crossOrigin = "anonymous"
        img.style.filter='blur(0px)';
    }
}
function analyzeProfileImage(img){
    img.crossOrigin = "anonymous"
    nsfwjs.load("/php/contentManagementSystem/Content-Management-System/model2/")
        .then(function (model) {
            return model.classify(img)
        })
        .then(function (predictions) {
            console.log('Analyzed Image: ' + img.src, predictions)
            if(
                (predictions[0].className=='Hentai' || predictions[0].className=='Porn' || predictions[0].className=='Sexy')
                && predictions[0].probability >= 0.5
            ){
                var editor = document.getElementById("editor");
                editor.style.display = "none";
                alertify.alert('Unable to Upload','Inappropriate content was found in the image file provided. Try another image or contact a moderator', function (){
                    location.reload();
                });
            }else{
                var confirmButton = document.getElementById("confirmButton");
                confirmButton.textContent = "Confirm";
                confirmButton.disabled = false;
            }
        })
}
function analyzeImage(){
    //alert("change detected");
    const preview = document.querySelector("[id='imageCheck']");
    const file = document.querySelector('input[type=file]').files[0];
    const reader = new FileReader();

    reader.addEventListener("load", function () {
        // convert image file to base64 string
        preview.src = reader.result;
    }, false);

    if (file) {
        reader.readAsDataURL(file);
    }
    analyze();
}
function analyze() {
    //checkImage
    const imgArray = document.getElementsByClassName('checkImage')
    for (let i = 0; i < imgArray.length; i++) {
        const img = imgArray[i]
        img.crossOrigin = "anonymous"
        nsfwjs.load("/php/contentManagementSystem/Content-Management-System/model2/")
            .then(function (model) {
                return model.classify(img)
            })
            .then(function (predictions) {
                console.log('Analyzed Image: ' + img.src, predictions)
                //alert('Analyzed Image: ' + img.src + predictions);
                if(
                    (predictions[0].className=='Hentai' || predictions[0].className=='Porn' || predictions[0].className=='Sexy')
                    && predictions[0].probability >= 0.5
                ){
                    //blurImage(img);
                    const probability = predictions[0].probability;
                    const imgBlock = document.getElementById('imageBlock');
                    const imageReason = document.getElementById('imageReason');
                    const imageBlockValue = document.getElementById('imageBlockValue');

                    imgBlock.value = "0";
                    imageReason.value = predictions[0].className;
                    imageBlockValue.value = probability;
                }
            })
    }
}