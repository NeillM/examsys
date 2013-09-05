var checkRow = function () {
  var collapse = $('#collapse').is(':checked');
  var caseSensitive = $('#casesensitive').is(':checked');
  var term = $('#keywords').val();

  if (term != '') {
    var regexpMod = (caseSensitive) ? 'g' : 'gi';
    var regexp = new RegExp('(' + term + ')', regexpMod); 
    var content = $(this).html();

    if (content.match(regexp)) {
      $(this).html(content.replace(regexp, '<span class="highlight">$1</span>'));
    }
  }
}

var doHighlight = function () {
  $('li.response').each(checkRow);
}

var cleanResponses = function () {
  $('li.response').each(function () {
    var content = $(this).html();
    $(this).html(content.replace(/<span class="highlight">([a-zA-Z]*)<\/span>/g, '$1'));
  });
}

$(function () {
  $('#highlight').click(function (e) {
    e.preventDefault();
    cleanResponses();
    doHighlight();
  })
});