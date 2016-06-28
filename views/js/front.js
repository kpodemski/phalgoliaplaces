var algoliaIsRunning = false;

$(function() {
  setTimeout(runAlgoliaPlaces, 1500);
});

$(document).ajaxComplete(function() {
  setTimeout(runAlgoliaPlaces, 1500);
});

function runAlgoliaPlaces() {
  if ($('#address1').length > 0 && algoliaIsRunning == false) {
   
    var algoliaPlacesOptions = {
      container: $('input#address1').get(0),
      language: phalgoliaplaces_isoLang,
      type: 'address',
      useDeviceLocation: false,
      templates: {
        value: function(suggestion) {
          return suggestion.name;
        }
      },
    };
    
    if (phalgoliaplaces_appId && phalgoliaplaces_apiKey) {
      algoliaPlacesOptions['appId'] = phalgoliaplaces_appId;
      algoliaPlacesOptions['apiKey'] = phalgoliaplaces_apiKey;
    }

    var placesAutocomplete = places(algoliaPlacesOptions);

    placesAutocomplete.on('change', function resultSelected(e) {
      $('#city').val(e.suggestion.city);
      $('#postcode').val(e.suggestion.postcode);

      if (e.suggestion.countryCode != '') {
        $.each(countries, function(index, country) {
          if (country.iso_code.toLowerCase() === e.suggestion.countryCode.toLowerCase()) {
            $('#id_country').val(country.id_country).trigger('change');
            if (country.contains_states == 1 && country.states.length > 0) {
              $.each(country.states, function(key, state) {
                if (state.name == e.suggestion.administrative) {
                  $('#id_state').val(state.id_state).trigger('change');
                }
              });
            }
          }
        });
      }
    });
    algoliaIsRunning = true;
  }
}
