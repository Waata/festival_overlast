// = require jquery
// = require bootstrap-sprockets
// = require_tree .


// alert("Welkom op de Beta site van Geluidoverlast")
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})

$('#btn-secondary').fatoggle(['fa-play','fa-pause'],OPTIONS);