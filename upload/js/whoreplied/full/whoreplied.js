/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

/**
 * Create the WhoReplied namespace, if it does not already exist.
 */
var WhoReplied = WhoReplied || {};

/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
  /**
   * Clone the original XenForo.PageNav object.
   */
  WhoReplied.XenForoPageNav = XenForo.PageNav;

  /**
   * Patch XenForo.PageNav to add overlay support.
   */
  WhoReplied.PageNav = function($pageNav)
  {
    WhoReplied.XenForoPageNav.call(this, $pageNav);

    if ($pageNav.parents('.xenOverlay').length) {
      $pageNav.find('a:not(.PageNavPrev,.PageNavNext)').each(
        function() {
          $(this).addClass('OverlayTrigger');
          $(this).data('cacheoverlay', 'true');
        }
      );

      $pageNav.children('nav').xfActivate();
    }
  };

  WhoReplied.PageNav.prototype = Object.create(
    WhoReplied.XenForoPageNav.prototype,
    {
      prevPage: {
        value: function(page)
        {
          WhoReplied.XenForoPageNav.prototype.prevPage.apply(this, arguments);

          this.api.getItemWrap().xfActivate();
        }
      },

      nextPage: {
        value: function(page)
        {
          WhoReplied.XenForoPageNav.prototype.nextPage.apply(this, arguments);

          this.api.getItemWrap().xfActivate();
        }
      },

      buildPageLink: {
        value: function(page)
        {
          var $pageLink = WhoReplied.XenForoPageNav.prototype.buildPageLink.apply(this, arguments);

          if (this.api.getItemWrap().parents('.xenOverlay').length) {
            $pageLink.addClass('OverlayTrigger');
            $pageLink.data('cacheoverlay', 'true');
          }

          return $pageLink;
        }
      }
    }
  );

  /**
   * Override core XenForo.PageNav with patched version.
   */
  XenForo.PageNav = WhoReplied.PageNav;

  /**
   * Override filter_list.js functions
   */
  XenForo.FilterList.prototype.filterAjax = function(ajaxData)
  {
    if (XenForo.hasResponseError(ajaxData))
    {
      return;
    }

    var $children = $(ajaxData.templateHtml).find('li.memberListItem');

    this.$groups.hide();
    this.$listItems.hide();
    if (this.lookUpUrl)
    {
      $('.PageNav').hide();
    }

    this.removeAjaxResults();

    if (!$children.length)
    {
      this.$listCounter.text(0);
      this.showHideNoResults(true);
    }
    else
    {
      this.$ajaxResults = $children;

      this.showHideNoResults(false);
      this.$list.append($children);
      $children.xfActivate();

      var $items = $children.filter('.listItem').add($children.find('.listItem')), items = [];
      $items.each(function(i, el) {
        items[i] = new XenForo.FilterListItem($(el));
      });
      this.applyFilter(items);
      this.$listCounter.text($items.length);
    }

    this.handleLast();

  }
}
(jQuery, this, document);
