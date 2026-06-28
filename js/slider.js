var slider = document.getElementById('slider');
var slides = slider.getElementsByClassName('slider');

function active_slide(index){
    slider.classList.remove('active');
    st = parseFloat(slider.getAttribute('name'));
    sn = slides.length;
    for(i=0;i<sn;i++){
        delay = parseFloat(i - index) - 1 / 6;
        slides[i].setAttribute('style','animation-delay:'+delay * st+"s;");
    }
    void slider.offsetWidth;
    slider.classList.add('active');
}