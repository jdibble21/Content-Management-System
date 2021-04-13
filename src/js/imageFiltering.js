// Auto blurs image if inappropriate content is detected, and creates block message for admin review
function blurImage(img) {
    img.style.filter='blur(50px)';
}
function unblurImages() {
    const imgArray = document.getElementsByClassName('filter')
    for (let i = 0; i < imgArray.length; i++) {
        const img = imgArray[i]
        img.crossOrigin = "anonymous"
        img.style.filter='blur(0px)';
    }
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
    Analyze();
}
function Analyze() {
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
                    && predictions[0].probability >= 0.8
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