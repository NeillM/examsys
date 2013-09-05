var getCheckRow = function () {
  var collapse = $('#collapse').is(':checked');
  var caseSensitive = $('#casesensitive').is(':checked');
  var term = $('#keywords').val();

  return function () {
    if (term != '') {
      var regexpMod = (caseSensitive) ? 'g' : 'gi';
      var regexp = new RegExp('(' + term + ')', regexpMod); 
      var content = $(this).html();

      var haveMatch = content.match(regexp);

      if (haveMatch || !collapse) {
        if (!$(this).is(':visible')) {
          $(this).slideDown('slow');
        }
      }

      if (haveMatch) {
        $(this).html(content.replace(regexp, '<span class="highlight">$1</span>'));
      } else if (collapse) {
        $(this).slideUp('slow');
      }
    }
  }
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
    var checkRow = getCheckRow();
    $('li.response').each(checkRow);
  })
});