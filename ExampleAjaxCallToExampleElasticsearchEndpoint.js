function getServerURL() {
  if(window.location.hostname == 'localhost') {
    return 'http://'+window.location.hostname+':20091';
  } else {
    return 'https://'+window.location.hostname;
  }
}

$(document).ready(function() {
  $('#searchInput').keyup(function() {
    if($(this).val() == '') {
      $('#requestAndSuggest').empty();
    } else {
      queryString = $(this).val();
      terms = listTerms(queryString);
      fullString = "";
      disambTable = "";
      if(terms.length > 0) {
        fullString = getQueryStringResults();
        if(terms.length > 1) {
          disambTable = disambiguationTable();
        }
      }
      $('#requestAndSuggest').load(getServerURL()+'/m/extensions/SMWCindyKate/SMWCindyKateEndPoint.php',
        {
          'q': queryString
        },
        function() {
        }
      );
    }
  });
});
