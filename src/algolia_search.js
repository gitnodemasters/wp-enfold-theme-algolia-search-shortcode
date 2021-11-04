/* global algoliasearch instantsearch */

// import { createDropdown } from './Dropdown';

/* global instantsearch */

// import {
//     hasClassName,
//     addClassName,
//     removeClassName,
//     capitalize,
//   } from './util';

function hasClassName(elem, className) {
  return elem.className.split(" ").indexOf(className) >= 0;
}

function addClassName(elem, className) {
  elem.className = [...elem.className.split(" "), className].join(" ");
}

function removeClassName(elem, className) {
  elem.className = elem.className
    .split(" ")
    .filter((name) => name !== className)
    .join(" ");
}

function capitalize(str) {
  if (typeof str !== "string") return "";
  return str.charAt(0).toUpperCase() + str.slice(1);
}

const CLASS_OPENED = "ais-Dropdown--opened";
const CLASS_BUTTON = "ais-Dropdown-button";
const CLASS_CLOSE_BUTTON = "ais-Dropdown-close";

const cx = (...args) => args.filter(Boolean).join(" ");

function createDropdown(
  baseWidget,
  {
    cssClasses: userCssClasses = {},
    buttonText,
    buttonClassName,
    closeOnChange,
  } = {}
) {
  // Merge class names with the default ones and the ones from user
  const cssClasses = {
    root: cx("ais-Dropdown", userCssClasses.root),
    button: cx(CLASS_BUTTON, userCssClasses.button),
    buttonRefined: cx(
      "ais-Dropdown-button--refined",
      userCssClasses.buttonRefined
    ),
    closeButton: cx(CLASS_CLOSE_BUTTON, userCssClasses.closeButton),
  };
  const makeWidget = instantsearch.widgets.panel({
    cssClasses,
    templates: {
      header: (options) => {
        const { widgetParams } = options;

        let text;
        if (typeof buttonText === "string") {
          text = buttonText;
        } else if (typeof buttonText === "function") {
          text = buttonText(options);
        } else {
          // See if the widget has `attribute`
          const attribute =
            widgetParams && widgetParams.attribute
              ? capitalize(widgetParams.attribute)
              : "";
          // Get the number of refinements if the widget has `items`
          const nbRefinements = (options.items || []).filter(
            (item) => item.isRefined
          ).length;
          // Format the button text
          text =
            nbRefinements > 0 ? `${attribute} (${nbRefinements})` : attribute;
        }

        const classNames = [cssClasses.button];
        if (typeof buttonClassName === "string") {
          classNames.push(buttonClassName);
        } else if (typeof buttonClassName === "function") {
          classNames.push(buttonClassName(options));
        } else if ((options.items || []).find((item) => item.isRefined)) {
          classNames.push(cssClasses.buttonRefined);
        }

        return `
          <button type="button" class="${cx(...classNames)}">
            ${text}
          </button>
        `;
      },
      footer: `<button type="button" class="${cssClasses.closeButton}">Apply</button>`,
    },
  })(baseWidget);

  return (widgetParams) => {
    const widget = makeWidget(widgetParams);
    let cleanUp;
    let state = {};

    // Return a modified version of the widget
    return {
      ...widget,
      init: (options) => {
        const rootElem = document
          .querySelector(widgetParams.container)
          .querySelector(".ais-Panel");
        const headerElem = rootElem.querySelector(".ais-Panel-header");
        const closeButtonElem = rootElem.querySelector(
          "." + CLASS_CLOSE_BUTTON
        );

        const open = () => {
          addClassName(rootElem, CLASS_OPENED);
          // This 'click' event is still being propagated,
          // so we add this event listener in the next tick.
          // Otherwise, it will immediately close the panel again.
          setTimeout(() => {
            state.windowClickListener = (event) => {
              // Close if the outside is clicked
              if (!rootElem.contains(event.target)) {
                close();
              }
            };
            // Add an event listener when the panel is opened
            window.addEventListener("click", state.windowClickListener);
          }, 0);
        };
        const close = () => {
          removeClassName(rootElem, CLASS_OPENED);
          // Remove the event listener when the panel is closed
          window.removeEventListener("click", state.windowClickListener);
          delete state.windowClickListener;
        };
        const isOpened = () => hasClassName(rootElem, CLASS_OPENED);
        const toggle = () => {
          if (isOpened()) {
            close();
          } else {
            open();
          }
        };

        // Add a click listener to the header (button)
        const buttonListener = (event) => {
          if (!event.target.matches("." + CLASS_BUTTON)) {
            return;
          }
          toggle();
        };
        headerElem.addEventListener("click", buttonListener);

        closeButtonElem.addEventListener("click", close);

        // Setup a clean-up function, which will be called in `dispose`.
        cleanUp = () => {
          headerElem.removeEventListener("click", buttonListener);
          if (state.windowClickListener) {
            window.removeEventListener("click", state.windowClickListener);
          }
        };

        // Whenever uiState changes, it closes the panel.
        options.instantSearchInstance.use(() => ({
          subscribe() {},
          unsubscribe() {},
          onStateChange() {
            if (
              isOpened() &&
              (closeOnChange === true ||
                (typeof closeOnChange === "function" &&
                  closeOnChange() === true))
            ) {
              close();
            }
          },
        }));
        return widget.init.call(widget, options);
      },
      dispose: (options) => {
        if (typeof cleanUp === "function") {
          cleanUp();
        }
        return widget.dispose.call(widget, options);
      },
    };
  };
}

var algolia_app_id = document.getElementById("algolia_app_id").value;
var algolia_search_api_key = document.getElementById(
  "algolia_search_api_key"
).value;
var algolia_index_pre = document.getElementById("algolia_index_pre").value;
var post_type = document.getElementById("post_type").value;

var indexName = algolia_index_pre + "searchable_posts",
  hitsPerPage = 15,
  facetingAfterDistinct = true,
  content_type_label = "",
  facet_attribute_type = "",
  facet_attribute_tag = "";

if (post_type == "resource") {
  content_type_label = 'post_type_label:"Resources"';
  facet_attribute_tag = "taxonomies.tag";
  facet_attribute_type = "taxonomies.archiveresources_type";
} else if (post_type == "blog") {
  hitsPerPage = 12;
  content_type_label = 'post_type_label:"Posts"';
  facet_attribute_tag = "taxonomies.post_tag";
  facet_attribute_type = "taxonomies.category";
} else if (post_type == "event") {
  indexName = algolia_index_pre + "posts_event";
  facetingAfterDistinct = false;
  facet_attribute_tag = "taxonomies.event_tag";
  facet_attribute_type = "taxonomies.event_type";
} else {
  facet_attribute_tag = "taxonomies.tag";
  facet_attribute_type = "taxonomies.archiveresources_type";
}

const searchClient = algoliasearch(algolia_app_id, algolia_search_api_key);

//only search in posts
// indexName = "wp3_posts_post";
// facet_attribute_tag = "taxonomies.post_tag";
// facet_attribute_type = "taxonomies.category";
// content_type_label = "";
const search = instantsearch({
  indexName,
  searchClient,
});

const MOBILE_WIDTH = 375;

const brandDropdown = createDropdown(instantsearch.widgets.menu, {
  // closeOnChange: true,
  closeOnChange: () => window.innerWidth >= MOBILE_WIDTH,
  // cssClasses: { root: "my-BrandDropdown" },
  // buttonText: "All Topics",
  buttonText({ items }) {
    const refinedItem = (items || []).find(
      (item) => item.label !== 'All' && item.isRefined
    );
    return refinedItem ? `${refinedItem.label}` : 'All Topics';
  },
});

const refinementListDropdown = createDropdown(
  // instantsearch.widgets.refinementList,
  instantsearch.widgets.menu,
  {
    // closeOnChange: true,
    closeOnChange: () => window.innerWidth >= MOBILE_WIDTH,
    // buttonText: "All Types",
    buttonText({ items }) {
      const refinedItem = (items || []).find(
        (item) => item.label !== 'All Types' && item.isRefined
      );
      return refinedItem ? `${refinedItem.label}` : 'All Types';
    },
  }
);

const blogItemTemplate = `
`;

search.addWidgets([
  instantsearch.widgets.configure({
    facetingAfterDistinct,
    filters: content_type_label,
    hitsPerPage,
    maxValuesPerFacet: 50,
  }),

  instantsearch.widgets.searchBox({
    container: "#searchbox",
    placeholder: "Search",
  }),
  
  instantsearch.widgets.hits({
    container: "#hits",
    templates: {
      item: (data) =>
        data.exclude_from_search
          ? null
          : post_type == 'resource' || post_type == 'blog' ? `
      <div class="inner-block">
        <div class="col-inner">
          <div class="featured-image" style="background-color:false;background-size:contain;background-image:url(${
            data.images.hasOwnProperty("large")
              ? data.images.large.url
              : data.images.hasOwnProperty("thumbnail")
              ? data.images.thumbnail.url
              : ""
          });"> <a href="customer-reviews-infographic"></a> </div>
          <div class="hr" style="background-color:#1b1554;">
            <div style="background:#4ea8df;width:100%;height:100%;display:block;"></div>
          </div>
          <div class="inner-text">
            ${post_type == 'blog' ? `<p class="category"><a href="${
              data.permalink
            }">Blog</a></p>` : ``}
            <h5>${instantsearch.highlight({
              attribute: "post_title",
              hit: data,
            })}</h5>
            <div class="the-excerpt"><p>${data.content} ${
              data.post_excerpt
            }</p></div>
            <div class="read-more"><a href="${
              data.permalink
            }">${data.taxonomies.archiveresources_type == 'Webinar' ? 'Watch Webinar' :  
            data.taxonomies.archiveresources_type == 'Video' ? 'Play Video' :
            data.taxonomies.archiveresources_type == 'Podcast' ? 'Listen Podcast' :
            data.taxonomies.archiveresources_type == 'Case Study' || data.taxonomies.archiveresources_type == 'White Paper' 
            || data.taxonomies.archiveresources_type == 'Solution Brief' ? 'Read ' + data.taxonomies.archiveresources_type :
             'View ' + data.taxonomies.archiveresources_type}</a></div>
          </div>
        </div>
      </div>
        ` : post_type == 'event' ? `
      <div class="inner-block resource-slide card-block-inner" style="background-color: 
            ${data.taxonomies.event_type == 'Webinar' ? 'rgb(121, 166, 247)' :
            data.taxonomies.event_type == 'Conferences' ? 'rgb(79, 229, 254)' :
            data.taxonomies.event_type == 'Field Event' ? 'rgb(59, 204, 253)' :
            data.taxonomies.event_type == 'Road Shows' ? 'rgb(125, 153, 211)' : ''};"> <!-- IF external_url  has-link -->
        <div class="logo-block-area">
          <img class="item-image" src="${data.images.company_logo.url}" alt="Webinar">
        </div>
        <h6 class="item-title">${data.post_title}</h6>
        <p class="event-location">
            ${data.post_date_formatted}
        </p>
        <p class="event-location">${data.event_location}</p>
        <div class="item-content content-styled">
          <span data-link="${data.permalink}" class="link">
            ${data.taxonomies.event_type == 'Webinar' ? 'Watch Webinar' :  
            data.taxonomies.event_type == 'Conferences' ? 'RSVP Now' :
            data.taxonomies.event_type == 'Field Event' ? 'RSVP Now' :
            data.taxonomies.event_type == 'Road Shows' ? 'RSVP Now' : ''}</span>
        </div>
      </div>
        ` : '',
      empty: "<h1>No results... please consider another query</h1>",
    },
  }),

  instantsearch.widgets.pagination({
    container: "#pagination",
    templates: {
      previous: '< view previous page',
      next: 'view more ' + post_type + 's >',
    }
  }),

  brandDropdown({
    container: "#topics",
    attribute: facet_attribute_tag,
    limit: 50,
    // searchable: true,
  }),
  refinementListDropdown({
    container: "#types",
    attribute: facet_attribute_type,
    limit: 50,
    // searchable: true,
  }),
]);

search.start();

// Filter Tags
// jQuery("#topics > div > div.ais-Panel-body > div > div > ul").change(function() {
//   console.log("*****************topics")
//   var filter_tag = 'text';
//   $(".filter-topic").hide();
//   if(filter_tag){
//     $(".populate-tags").append('<div class="filter-tag filter-topic" data-filter-tag="'+filter_tag+'">'+filter_tag+'<svg width="14px" height="14px" viewBox="0 0 14 14" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg"><path d="M0 6.912C0 3.0936 3.0888 0 6.9066 0C10.7292 0 13.818 3.0936 13.818 6.912C13.818 10.725 10.7292 13.8234 6.9066 13.8234C3.0888 13.8234 0 10.725 0 6.912ZM10.1467 10.8936L10.8895 10.1514L7.65131 6.912L10.8895 3.672L10.1467 2.9298L6.90911 6.1692L3.67151 2.9298L2.92871 3.672L6.16691 6.912L2.92871 10.1514L3.67151 10.8936L6.90911 7.6542L10.1467 10.8936Z" id="Fill-1-Copy" fill="#2A96EE" fill-rule="evenodd" stroke="none" /></svg></a>');
//   }
//   $(".filter-tags").show();
// });

// $("#facet-type-dropdown select").change(function() {
//   var filter_tag = $(this).val();
//   $(".filter-type").hide();
//   if(filter_tag){
//     $(".populate-tags").append('<div class="filter-tag filter-type" data-filter-tag="'+filter_tag+'">'+filter_tag+'<svg width="14px" height="14px" viewBox="0 0 14 14" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg"><path d="M0 6.912C0 3.0936 3.0888 0 6.9066 0C10.7292 0 13.818 3.0936 13.818 6.912C13.818 10.725 10.7292 13.8234 6.9066 13.8234C3.0888 13.8234 0 10.725 0 6.912ZM10.1467 10.8936L10.8895 10.1514L7.65131 6.912L10.8895 3.672L10.1467 2.9298L6.90911 6.1692L3.67151 2.9298L2.92871 3.672L6.16691 6.912L2.92871 10.1514L3.67151 10.8936L6.90911 7.6542L10.1467 10.8936Z" id="Fill-1-Copy" fill="#2A96EE" fill-rule="evenodd" stroke="none" /></svg></a>');
//   }
//   $(".filter-tags").show();
// });

// // Clear Filters
// $('body').on('click', '.clear-selected', function() {
//   $("#facet-type-dropdown select").prop('selectedIndex',0).trigger("change");
//   $("#facet-topic-dropdown select").prop('selectedIndex',0).trigger("change");
//   $(".filter-type").hide();
//   $(".filter-topic").hide();
//   $('.filter-tags').hide();
// });

// $('body').on('click', '.filter-type', function() {
//   $(".filter-type").hide();
//   $("#facet-type-dropdown select").prop('selectedIndex',0).trigger("change");
//   if(!$("#facet-topic-dropdown select").val()) {
//     $('.filter-tags').hide();
//   }
// });