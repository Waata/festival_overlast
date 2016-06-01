// = require jquery
// = require bootstrap-sprockets
// = require_tree .


// alert("Welkom op de Beta site van Geluidoverlast")
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})

//TOGGLE FONT AWESOME ON CLICK
$('.faq-links').click(function(){
    $(this).find('i').toggleClass('fa-plus-square-o fa-2x fa-minus-square-o fa-2x')
});
$('.faq-links').blur(function(){
    $(this).find('i').toggleClass('fa-plus-square-o fa-2x fa-minus-square-o fa-2x')
});