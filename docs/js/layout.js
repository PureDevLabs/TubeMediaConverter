$(document).ready(function () {
    var AFFIX_TOP_LIMIT = 300;
    var AFFIX_OFFSET = 49;

    var $menu = $("#menu"),
		$btn = $("#menu-toggle");

    $("#menu-toggle").on("click", function () {
        $menu.toggleClass("open");
        return false;
    });


    $(".docs-nav").each(function () {
        var $affixNav = $(this),
			$container = $affixNav.parent(),
			affixNavfixed = false,
			originalClassName = this.className,
			current = null,
			$links = $affixNav.find("a");

        function getClosestHeader(top) {
            var last = $links.first();

            if (top < AFFIX_TOP_LIMIT) {
                return last;
            }

            for (var i = 0; i < $links.length; i++) {
                var $link = $links.eq(i),
					href = $link.attr("href");

                if (href.charAt(0) === "#" && href.length > 1) {
                    var $anchor = $(href).first();

                    if ($anchor.length > 0) {
                        var offset = $anchor.offset();

                        if (top < offset.top - AFFIX_OFFSET) {
                            return last;
                        }

                        last = $link;
                    }
                }
            }
            return last;
        }

		function getVisible(obj)
		{
			var elH = obj.outerHeight(),
			H   = $(window).height(),
			r   = obj[0].getBoundingClientRect(), t=r.top, b=r.bottom;
			return Math.max(0, t>0? Math.min(elH, H-t) : (b<H?b:H));
		}

        $(window).on("scroll load", function (evt) {
            var top = window.scrollY,
		    	height = $affixNav.outerHeight(),
		    	max_bottom = $container.offset().top + $container.outerHeight(),
		    	bottom = top + height + AFFIX_OFFSET;

			var maxHt = getVisible($affixNav);
			//console.log(maxHt);
			var maxHtVal = (maxHt >= $(window).height() - AFFIX_OFFSET) ? maxHt + "px" : "auto";
			$affixNav.css("max-height", maxHtVal);

            if (affixNavfixed) {
                if (top <= AFFIX_TOP_LIMIT) {
                    $affixNav.removeClass("fixed");
                    $affixNav.css("top", 0);
                    affixNavfixed = false;
                } else if (bottom > max_bottom) {
                    $affixNav.css("top", (max_bottom - height) - top);
                } else {
                    $affixNav.css("top", AFFIX_OFFSET);
                }
            } else if (top > AFFIX_TOP_LIMIT) {
                $affixNav.addClass("fixed");
                affixNavfixed = true;
            }

            var $current = getClosestHeader(top);
            //console.log(evt);
            if (evt.type == "load") $affixNav.scrollTop($current.position().top);

            if (current !== $current) {
                $affixNav.find(".active").removeClass("active");
                $current.addClass("active");
                current = $current;
            }
        });
    });

    prettyPrint();
});
