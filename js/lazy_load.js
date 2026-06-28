function insta_data(){
    url = base_url+"modules/main/insta_data.php";
    var xmlHR = new XMLHttpRequest();
    xmlHR.open("get", url, false);
    xmlHR.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHR.send();
    return xmlHR.responseText;
}
function script_insta_data(){
    var tag = document.createElement("script");
    tag.src = "//www.instagram.com/embed.js";
    document.getElementsByTagName("head")[0].appendChild(tag);
}

function comment_data(){
    url = base_url+"modules/main/comment_data.php";
    var xmlHR = new XMLHttpRequest();
    xmlHR.open("get", url, false);
    xmlHR.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHR.send();
    return xmlHR.responseText;
}
if(!!window.IntersectionObserver){
    
    let observer = new IntersectionObserver((entries, observer) => { 
        entries.forEach(entry => {
        if(entry.isIntersecting){
            switch(entry.target.id){
                case 'insta_holder':
                    entry.target.innerHTML = insta_data();
                    script_insta_data();
                    break;
                case 'comment_box':
                    entry.target.innerHTML = entry.target.innerHTML + comment_data();
                    break;
            }
            observer.unobserve(entry.target);
        }
        });
    }, {rootMargin: "0px 0px -200px 0px"});
    insta_box = document.getElementById("insta_holder");
    comment_box = document.getElementById("comment_box");
    observer.observe(insta_box);
    observer.observe(comment_box);
}
