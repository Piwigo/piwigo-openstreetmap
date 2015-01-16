/*
 *  qleaflet js Maps Plugin
 *  Wrapper for leaflet.js library
 *  https://github.com/hamsterbacke23/qleaflet
 *  Author: Seitenbau GmbH (tobias.schmidt@seitenbau.com)
 *  Modify by: xbgmsharp (xbgmsharp@gmail.com))
 *  PWG batch manager integration
 */
;(function ( $, window, document, undefined ) {

    var pluginName = "qleaflet",
        defaults = {
          providers: [{
            providerName: 'OpenStreetMap',
            variantName: false,
            noWrap:  false
          }],
          leafletJsUri    : 'plugins/piwigo-openstreetmap/leaflet/leaflet.js',
          leafletCssUri   : 'plugins/piwigo-openstreetmap/leaflet/leaflet.css',
          leafletImageUri : 'plugins/piwigo-openstreetmap/leaflet/images/',
          retina          : true,
          markers         : [],
          mapOptions: {
              center: [0,0],
              zoom: 1,
              scrollWheelZoom : true,
              worldCopyJump: true,
              contextmenu: false
            },
          formid: false
        };

    // The actual plugin constructor
    function Plugin( element, options ) {
        this.element   = $(element);
        this.options   = $.extend( {}, defaults, options );
        if(typeof options != 'undefined' && typeof options.mapOptions != 'undefined') {
          this.options.mapOptions = $.extend( {}, defaults.mapOptions, options.mapOptions );
        }
        this._defaults = defaults;
        this._name     = pluginName;

        this.init();
    }

    Plugin.prototype = {

      loadStylesheet : function(url) {
        if($('link').filter('[href="'+url+'"]').length == 0) {
          $('<link>').attr('rel','stylesheet')
            .attr('type','text/css')
            .attr('href',url)
            .prependTo('head');
        }
      },

      setMap : function() {
        var options               = this.options;
        options.mapOptions.center = this.element.center;
        var mmap = window.L.map; //IE 8
        this.map                  = mmap(this.id, options.mapOptions);
        var providerData = {};

        for (var i = 0; i < options.providers.length; i++) {
          providerData = this.getProviderData(options.providers[i]);
          window.L.tileLayer(providerData.url, providerData.options).addTo(this.map);
        };

        window.L.control.scale().addTo(this.map);
        this.map._onResize();
        this.setResizeHandler();
        this.setMarkers();
        this.map._onResize();

	var popup = window.L.popup();

	function onMapClick(e) {
                //console.log(e.latlng.toString());
                //console.log(document.forms);
		//console.log(this.options);
  		popup
			.setLatLng(e.latlng)
			.setContent("You clicked the map at " + e.latlng.toString())
			.openOn(this.map);

		if(this.options.formid != false) { // Batch manager single mode
			$('[name=osmlat-'+this.options.formid+']').val(Math.ceil(e.latlng.lat * 100000) / 100000);
			$('[name=osmlon-'+this.options.formid+']').val(Math.ceil(e.latlng.lng * 100000) / 100000);
		} else { // Batch manager global mode
			var form=document.forms[1];
			form.osmlat.value = Math.ceil(e.latlng.lat * 100000) / 100000;
			form.osmlon.value = Math.ceil(e.latlng.lng * 100000) / 100000;
		}
	}

        this.map.on('click', onMapClick, this);
      },

      bind : function(fn, scope) {
          return function () {
              fn.apply(scope, arguments);
          };
      },

      setMarkers : function() {
        if(this.element.markers.length === 0) {
          return;
        }
        if(this.options.leafletImageUri) {
          window.L.Icon.Default.imagePath = this.options.leafletImageUri;
        }
        for (var i = 0; i < this.element.markers.length; i++) {
          window.L.marker(this.element.markers[i].pos).addTo(this.map)
            .bindPopup(this.element.markers[i].text, {className: 'map-standort'})
            .openPopup();
        };
      },

      setResizeHandler : function() {
        var self = this;
        $(window).on('orientationchange pageshow resize', function () {
            $('#' + this.id).height($(window).height());
            self.map.invalidateSize();
            self.map.setView(self.element.center, self.options.mapOptions.zoom);
        }).trigger('resize');
      },

      init : function() {
        this.id = this.element.attr('id');
        //add id if necessary
        if(!this.id) {
          this.id = 'leafletmap' + Math.floor((Math.random()*100)+1);
          this.element.attr('id', this.id);
        }
        // get data from data attributes in html
        // set markers for each map
        // other options are shared
        if(typeof this.element.data('markerpos') != 'undefined' && this.element.data('markerpos').length > 1) {
          this.element.markers = [];
          this.element.markers.push({
            pos : this.element.data('markerpos').split(','),
            text: this.element.data('markertext')
          });
          this.options.mapOptions.zoom = 15; // If position is set then zoom into it
        } else {
          this.element.markers = this.options.markers;
        }

        // set center
        if(typeof this.element.data('center') != 'undefined' && this.element.data('center').length != 0) {
          this.element.center = this.element.data('center').split(',');
        } else if (typeof this.element.data('markerpos') != 'undefined' && this.element.data('markerpos').length > 1) {
          this.element.center = this.element.data('markerpos').split(',');
        } else {
          this.element.center = this.options.mapOptions.center;
        }

        // formid for batch manager single mode
        if(typeof this.element.data('formid') != 'undefined' && this.element.data('formid').length != 0) {
          this.options.formid = this.element.data('formid');
        }

        //render
        this.loadStylesheet(this.options.leafletCssUri);
        $.getScript(this.options.leafletJsUri, this.bind(this.setMap, this));

	$(window).resize();
      },

      /* Taken from https://github.com/leaflet-extras/leaflet-providers/blob/master/leaflet-providers.js */
      getProviderData : function (providerData) {
        var variantName = providerData.variantName,
            providerName = providerData.providerName;

        var provider = {
              url: this.providers[providerName].url,
              options: this.providers[providerName].options
            };

            // overwrite values in provider from variant.
        if (variantName && 'variants' in this.providers[providerName]) {
          if (!(variantName in this.providers[providerName].variants)) {
            throw 'No such name in provider (' + variantName + ')';
          }
          var variant = this.providers[providerName].variants[variantName];
          provider = {
            url: variant.url || provider.url,
            options: L.Util.extend({}, provider.options, variant.options)
          };
        } else if (typeof provider.url === 'function') {
          provider.url = provider.url(parts.splice(1).join('.'));
        }

        // replace attribution placeholders with their values from toplevel provider attribution.
        var attribution = provider.options.attribution;
        var self = this;
        if (attribution.indexOf('{attribution.') !== -1) {
          provider.options.attribution = attribution.replace(/\{attribution.(\w*)\}/,
            function (match, attributionName) {
              return self.providers[attributionName].options.attribution;
            });
        }

        return provider;
      },

      providers :  {
        /* More Providers: https://github.com/leaflet-extras/leaflet-providers/blob/master/leaflet-providers.js */
        OpenStreetMap: {
          url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
          options: {
            attribution:
              '&copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
              '<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>'
          },
          variants: {
            Mapnik: {},
            BlackAndWhite: {
              url: 'https://{s}.www.toolserver.org/tiles/bw-mapnik/{z}/{x}/{y}.png'
            },
            DE: {
              url: 'https://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png'
            }
          }
        },
        MapQuestOpen :  {
            url: 'https://otile{s}.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.jpeg',
            options: {
              attribution:
                'Tiles Courtesy of <a href="http://www.mapquest.com/">MapQuest</a> &mdash; ' +
                'Map data {attribution.OpenStreetMap}',
              subdomains: '1234'
            },
            variants: {
              OSM: {},
              Aerial: {
                url: 'https://oatile{s}.mqcdn.com/tiles/1.0.0/sat/{z}/{x}/{y}.jpg',
                options: {
                  attribution:
                    'Tiles Courtesy of <a href="http://www.mapquest.com/">MapQuest</a> &mdash; ' +
                    'Portions Courtesy NASA/JPL-Caltech and U.S. Depart. of Agriculture, Farm Service Agency'
                }
              }
            }
        },
        OpenWeatherMap: {
            options: {
              attribution: 'Map data &copy; <a href="http://openweathermap.org">OpenWeatherMap</a>',
              opacity: 0.5
            },
            variants: {
              Clouds: {
                url: 'https://{s}.tile.openweathermap.org/map/clouds/{z}/{x}/{y}.png'
              },
              CloudsClassic: {
                url: 'https://{s}.tile.openweathermap.org/map/clouds_cls/{z}/{x}/{y}.png'
              },
              Precipitation: {
                url: 'https://{s}.tile.openweathermap.org/map/precipitation/{z}/{x}/{y}.png'
              },
              PrecipitationClassic: {
                url: 'https://{s}.tile.openweathermap.org/map/precipitation_cls/{z}/{x}/{y}.png'
              },
              Rain: {
                url: 'https://{s}.tile.openweathermap.org/map/rain/{z}/{x}/{y}.png'
              },
              RainClassic: {
                url: 'https://{s}.tile.openweathermap.org/map/rain_cls/{z}/{x}/{y}.png'
              },
              Pressure: {
                url: 'https://{s}.tile.openweathermap.org/map/pressure/{z}/{x}/{y}.png'
              },
              PressureContour: {
                url: 'https://{s}.tile.openweathermap.org/map/pressure_cntr/{z}/{x}/{y}.png'
              },
              Wind: {
                url: 'https://{s}.tile.openweathermap.org/map/wind/{z}/{x}/{y}.png'
              },
              Temperature: {
                url: 'https://{s}.tile.openweathermap.org/map/temp/{z}/{x}/{y}.png'
              },
              Snow: {
                url: 'https://{s}.tile.openweathermap.org/map/snow/{z}/{x}/{y}.png'
              }
            }
          },
      }
    };

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, "plugin_" + pluginName) || (typeof options != 'undefined' && options.reset === true)) {
              $.data(this, "plugin_" + pluginName, new Plugin( this, options ));
            }
        });
    };

})( jQuery, window, document );
