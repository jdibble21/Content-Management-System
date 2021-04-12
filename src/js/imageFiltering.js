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

function Analyze() {
    const imgArray = document.getElementsByClassName('filter')
    for (let i = 0; i < imgArray.length; i++) {
        const img = imgArray[i]
        img.crossOrigin = "anonymous"
        nsfwjs.load("/php/contentManagementSystem/Content-Management-System/model2/")
            .then(function (model) {
                return model.classify(img)
            })
            .then(function (predictions) {
                console.log('Analyzed Image: ' + img.src, predictions)
                if(
                    predictions[0].className=='Hentai' || predictions[0].className=='Porn' || predictions[0].className=='Sexy'
                    && predictions[0].probability >= 0.8
                ){
                    blurImage(img);
                    console.log('Blurred Image: ' + img.src)
                    //Ajax here to block post and create block message
                }

            })
    }
}