var algoliaIsRunning = false;

$(function() {
  setTimeout(runAlgoliaPlaces, 1500);
});

$(document).ajaxComplete(function() {
  setTimeout(runAlgoliaPlaces, 1500);
});

function runAlgoliaPlaces() {
  if ($('#address1').length > 0 && algoliaIsRunning == false) {
    var placesAutocomplete = places({
      container: $('input#address1').get(0),
      language: phalgoliaplaces_isoLang,
      type: 'address',
      useDeviceLocation: false,
      templates: {
        value: function(suggestion) {
          return suggestion.name;
        }
      },
    });

    placesAutocomplete.on('change', function resultSelected(e) {
      $('#city').val(e.suggestion.city);
      $('#postcode').val(e.suggestion.postcode);

      if (e.suggestion.countryCode != '') {
        $.each(countries, function(index, country) {
          if (country.iso_code.toLowerCase() === e.suggestion.countryCode.toLowerCase()) {
            $('#id_country').val(country.id_country).trigger('change');
          }
        });
      }
    });
    algoliaIsRunning = true;
  }
}
