/*  Special thanks to @peledies for this code: https://github.com/peledies/google-places */
(function($) {

    $.reviews_sgr_googlePlaces = function(element, options) {

        var defaults = {
              placeId: '' // placeId provided by google api documentation
            , render: ['reviews']
            , min_rating: 0
            , max_rows: 0
            , rotateTime: false
        };

        var plugin = this;

        plugin.settings = {}

        var $element = $(element),
             element = element;

        plugin.init = function() {
          plugin.settings = $.extend({}, defaults, options);
          plugin.settings.schema = $.extend({}, defaults.schema, options.schema);
          $element.html("<div id='map-plug'></div>"); // create a plug for google to load data into
          initialize_place(function(place){
            plugin.place_data = place;
            // render specified sections
            if(plugin.settings.render.indexOf('reviews') > -1){
              renderReviews(plugin.place_data.reviews);
              if(!!plugin.settings.rotateTime) {
                  initRotation();
              }
            }
            
            // render schema markup
            addSchemaMarkup(
                capture_element(plugin.settings.schema.displayElement)
              , plugin.place_data
            );

          });
        }

        var capture_element = function(element){
          if(element instanceof jQuery){
            return element;
          }else if(typeof element == 'string'){
            try{
              var ele = $(element);
              if( ele.length ){
                return ele;  
              }else{
                throw 'Element [' + element + '] couldnt be found in the DOM. Skipping '+element+' markup generation.';
              }
            }catch(e){
              console.warn(e); 
            } 
          }
        }

        var initialize_place = function(c){
          var map = new google.maps.Map(document.getElementById('map-plug'));

          var request = {
            placeId: plugin.settings.placeId
          };

          var service = new google.maps.places.PlacesService(map);

          service.getDetails(request, function(place, status) {
            if (status == google.maps.places.PlacesServiceStatus.OK) {
              c(place);
            }
          });
        }

        var sort_by_date = function(ray) {
          ray.sort(function(a, b){
            var keyA = new Date(a.time),
            keyB = new Date(b.time);
            // Compare the 2 dates
            if(keyA < keyB) return -1;
            if(keyA > keyB) return 1;
            return 0;
          });
          return ray;
        }

        var filter_minimum_rating = function(reviews){
          for (var i = reviews.length -1; i >= 0; i--) {
            if(reviews[i].rating < plugin.settings.min_rating){
              reviews.splice(i,1);
            }
          }
          return reviews;
        }

        var renderReviews = function(reviews){
          reviews = sort_by_date(reviews);
          reviews = filter_minimum_rating(reviews);
          var html = "";
          var row_count = (plugin.settings.max_rows > 0)? plugin.settings.max_rows - 1 : reviews.length - 1;
          // make sure the row_count is not greater than available records
          row_count = (row_count > reviews.length-1)? reviews.length -1 : row_count;
          for (var i = row_count; i >= 0; i--) {
            var stars = renderStars(reviews[i].rating);
            var date = convertTime(reviews[i].time);
            html = html+ "<div class='review-item'><blockquote><p class='review-text'><q>"+reviews[i].text+"</q></p><span class='review-author'>- "+reviews[i].author_name+"</span>"+stars+"</blockquote></div>"};
          $element.append(html);
        }
        
        var renderHours = function(element, data){
          if(element instanceof jQuery){
            var html = "<ul>";
            data.weekday_text.forEach(function(day){
              html += "<li>"+day+"</li>";
            });
            html += "</ul>";
            element.append(html);
          }         
        }

        var initRotation = function() {
            var $reviewEls = $element.children('.review-item');
            var currentIdx = $reviewEls.length > 0 ? 0 : false;
            $reviewEls.hide();
            if(currentIdx !== false) {
                $($reviewEls[currentIdx]).show();
                $($reviewEls[currentIdx]).addClass('fullcol');
                setInterval(function(){ 
                    if(++currentIdx >= $reviewEls.length) {
                        currentIdx = 0;
                    }
                    $reviewEls.hide();
                    $reviewEls.removeClass('fullcol');
                    $($reviewEls[currentIdx]).fadeIn('slow');
                    $($reviewEls[currentIdx]).addClass('fullcol');
                }, plugin.settings.rotateTime);
            }
        }

        var renderStars = function(rating){
          var stars = "<div class='review-stars'><ul>";
                            
          // fill in gold stars
          for (var i = 0; i < rating; i++) {
            stars = stars+"<li><i class='star'></i></li>";
          };

          // fill in empty stars
          if(rating < 5){
            for (var i = 0; i < (5 - rating); i++) {
              stars = stars+"<li><i class='star inactive'></i></li>";
            };
          }
          stars = stars+"</ul></div>";
          return stars;
        }

        var convertTime = function(UNIX_timestamp){
          var a = new Date(UNIX_timestamp * 1000);
          var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
          var time = months[a.getMonth()] + ' ' + a.getDate() + ', ' + a.getFullYear();
          return time;
        }
        
        var addSchemaMarkup = function(element, placeData) {
          var reviews = placeData.reviews;
          var lastIndex = reviews.length - 1;
          var reviewPointTotal = 0;
          var schema = plugin.settings.schema;
          for (var i = lastIndex; i >= 0; i--) {
            reviewPointTotal += reviews[i].rating;
          };
          // Set totals and averages - may be used later.
          var averageReview = reviewPointTotal / ( reviews.length );
          if(element instanceof jQuery){
            element.append( '<span itemscope="" itemtype="http://schema.org/' + schema.type + '">'
            +  '<meta itemprop="url" content="' + location.origin + '">'
            +  ' <span itemprop="name">' + placeData.name + '</span> '
            +  '<span itemprop="aggregateRating" itemscope="" itemtype="http://schema.org/AggregateRating">'
            +    '<span itemprop="ratingValue">' + averageReview.toFixed(2) + '</span>/<span itemprop="bestRating">5</span> '
            +  ' <span itemprop="ratingCount">' + reviews.length + '</span> '
            +  '</span>'
            +'</span>');
          }
        }
        
        plugin.init();
        
    }

    $.fn.reviews_sgr_googlePlaces = function(options) {

        return this.each(function() {
            if (undefined == $(this).data('reviews_sgr_googlePlaces')) {
                var plugin = new $.reviews_sgr_googlePlaces(this, options);
                $(this).data('reviews_sgr_googlePlaces', plugin);
            }
        });

    }

})(jQuery);
