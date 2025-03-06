$(document).ready(function(){
    $('.sec4').slick({
        slidesToShow: 3, // Number of slides to show at a time
        slidesToScroll: 1, // Number of slides to scroll at a time
        autoplay: false, // Auto play the carousel
        
        dots: true, // Show navigation dots
        responsive: [
            {
                breakpoint: 768, // Breakpoint for medium devices
                settings: {
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 576, // Breakpoint for small devices
                settings: {
                    slidesToShow: 1
                }
            }
        ]
    });
});