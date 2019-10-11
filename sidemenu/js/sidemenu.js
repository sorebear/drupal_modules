var $ = jQuery;

// document.addEventListener('DOMContentLoaded', function() {
//   var sideMenu = new SideMenu();
//   sideMenu.init();
// });

(function($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.sidemenu = {
    attach: function(context, settings) {
      
    }
  }
})(jQuery, Drupal, drupalSettings);

function Test() {
  this.init = function() {
    console.log('Test Constructor');
  }
}

function SideMenu() {
  this.animationSpeed = 5;
  this.sideMenuActiveTrail = document.querySelectorAll('.field--name-field-column-side-menu li.dropdown.active-trail');

  this.init = function() {
    var menus = document.querySelectorAll(' nav[role=navigation]');

    for (var i = 0; i < menus.length; i += 1) {
      var listItemsWithoutDropdown = menus[i].querySelectorAll('li:not(.dropdown):not(.menu-item)');
      var listItemsWithDropdown = menus[i].querySelectorAll('li.dropdown');
      
      this.addMenuItemClickHandlers(listItemsWithDropdown, true);
      this.addMenuItemClickHandlers(listItemsWithoutDropdown, false);
    }

    if (this.sideMenuActiveTrail[0] && window.innerWidth > 1200) {
      this.clickActiveTrail(0);
    }
  }

  this.clickActiveTrail = function(index) {
    var that = this;
    this.sideMenuActiveTrail[index].click();
    this.sideMenuActiveTrail[index].addEventListener('transitionend', function() {
      if (that.sideMenuActiveTrail[index + 1]) {
        that.clickActiveTrail(index + 1);
      }
    }, { once: true })
  }

  this.animateHeight = function(element, newHeight, callback) {
    var that = this;
    var start = null;
    var startingHeight = element.offsetHeight;
    var range = newHeight - startingHeight;

    function step(timestamp) {
      if (!start) start = timestamp;
      var progress = Math.min(1, (timestamp - start) / that.animationSpeed);

      element.style.height = startingHeight + progress * range + 'px';

      if (progress < 1) {
        window.requestAnimationFrame(step);
      } else {
        if (callback) callback();
      };
    }

    window.requestAnimationFrame(step);
  }

  this.addMenuItemClickHandlers = function(menuItems, hasDropdown) {
    var that = this;
    for (var j = 0; j < menuItems.length; j += 1) {
      menuItems[j].addEventListener('click', function(e) {
        e.stopImmediatePropagation();
        e.stopPropagation();

        if (hasDropdown) {
          that.animateSideMenu(e.currentTarget);
        }
      });
    } 
  }

  this.animateSideMenu = function(listItem) {
    var listItemHeight = listItem.querySelector('a').offsetHeight;
    var subMenuHeight = listItem.querySelector('ul.nav').offsetHeight;
    
    var $listItem = $(listItem);
    var $grandParentListItem = $listItem.parent().parent();

    if ($listItem.hasClass('dropdown-active')) {
      listItem.classList.remove('dropdown-active');

      this.animateHeight(listItem, listItemHeight);

      if ($grandParentListItem.hasClass('dropdown')) {
        var siblingHeight = this.getSiblingHeight($listItem);
        this.collapseGrandparentItem($grandParentListItem[0], listItemHeight + siblingHeight);
      }
    } else {
      this.animateHeight(listItem, subMenuHeight + listItemHeight, function() {
        listItem.classList.add('dropdown-active');
      });
  
      if ($grandParentListItem.hasClass('dropdown')) {
        this.expandGrandparentItem($grandParentListItem[0], subMenuHeight);
      }
    }
  }

  this.collapseGrandparentItem = function(listItem, grandchildHeight) {
    var linkHeight = listItem.querySelector('a').offsetHeight;
    var siblingHeight = this.getSiblingHeight($(listItem));

    listItem.style.height = linkHeight + grandchildHeight + "px";

    var $grandParentListItem = $(listItem).parent().parent();
    if ($grandParentListItem.hasClass('dropdown')) {
      this.collapseGrandparentItem($grandParentListItem[0], linkHeight + grandchildHeight + siblingHeight );
    }
  }

  this.getSiblingHeight = function($origin) {
    var $nextSibling = $origin.next();
    var $prevSibling = $origin.prev();
    var additionalHeight = 0;

    while ($prevSibling.length > 0) {
      additionalHeight += $prevSibling.height();
      $prevSibling = $prevSibling.prev();
    }

    while ($nextSibling.length > 0) {
      additionalHeight += $nextSibling.height();
      $nextSibling = $nextSibling.next();
    }

    return additionalHeight;
  }

  this.expandGrandparentItem = function(listItem, submenuHeight) {
    listItem.style.height = listItem.offsetHeight + submenuHeight + 'px';

    var $grandParentListItem = $(listItem).parent().parent();
    if ($grandParentListItem.hasClass('dropdown')) {
      this.expandGrandparentItem($grandParentListItem['0'], submenuHeight);
    }
  }
}