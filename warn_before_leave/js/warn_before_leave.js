document.addEventListener('DOMContentLoaded', function() {
  function WarnBeforeLeave() {
    this.body = document.querySelector('body');
    this.allLinks = this.body.querySelectorAll('body a');
    this.externalLinks = [];
    this.mask;
    this.modal;
    this.confirmButton;
    this.cancelButton;
    this.proposedLink = '';
    this.moduleSettings = drupalSettings.warnBeforeLeave;
  
    this.init = function() {
      this.addEventListenersToLinks();
      this.createDomElements();
    }
  
    this.addEventListenersToLinks = function() {
      var that = this;
      this.allLinks.forEach(function(link) {
        var parsedLink = link.href.replace('//www.', '//');
        var linkIsNotInternal = link.href.indexOf(location.origin) !== -1 && parsedLink.indexOf(location.origin) !== -1;
        if (!linkIsNotInternal) {
          linkIsExternal = link.href.indexOf('http') !== -1 ;
          if (linkIsExternal) {
            link.addEventListener('click', function(e) {
              if (that.moduleSettings.whitelistedSites.indexOf(link.href) !== -1) {
                e.preventDefault();
                window.open(link.href, '_blank');
              } else {
                that.proposedLink = link.href;
                that.appendDomElements(e);
              }
            });
          }
        }
      });
    }
  
    this.addAdditionalClasses = function(element, classesString) {
      if (classesString && classesString.length > 0) {
        classArr = classesString.split(' ');
        classArr.forEach(function(classString) {
          element.classList.add(classString);
        });
      }
    }
  
    this.createDomElements = function() {
      this.mask = document.createElement('div');
      this.mask.id = 'external-link-modal-mask';
      this.mask.classList.add('external-link-modal-mask');
  
      this.modal = document.createElement('div');
      this.modal.id = 'external-link-modal';
      this.modal.classList.add('external-link-modal');
      this.modal.innerHTML = this.moduleSettings.message.value;
  
      this.cancelButton = document.createElement('button');
      this.cancelButton.id = 'external-link-modal-button';
  
      this.cancelButton.classList.add('btn');
      this.cancelButton.classList.add('color-dark-accent');
      this.addAdditionalClasses(this.cancelButton, this.moduleSettings.cancelClasses);
      this.cancelButton.classList.add('btn');
      this.cancelButton.classList.add('color-dark-accent');
      this.cancelButton.classList.add('external-link-modal-button');
      this.cancelButton.innerText = this.moduleSettings.cancelText;
      this.cancelButton.addEventListener('click', this.closeModal.bind(this));
      
      this.confirmButton = document.createElement('button');
      this.confirmButton.id = 'external-link-modal-button';
      this.confirmButton.classList.add('btn');
      this.confirmButton.classList.add('external-link-modal-button');
      this.addAdditionalClasses(this.confirmButton, this.moduleSettings.confirmClasses);
      this.confirmButton.innerText = this.moduleSettings.confirmText;
      this.confirmButton.addEventListener('click', this.openExternalPage.bind(this));
    }
  
    this.openExternalPage = function() {
      this.closeModal();
      window.open(this.proposedLink, '_blank');
    }
  
    this.closeModal = function() {
      this.body.removeChild(this.mask);
    }
  
    this.appendDomElements = function(e) {
      e.preventDefault();
  
      this.body.appendChild(this.mask);
      this.mask.appendChild(this.modal);
      this.modal.appendChild(this.cancelButton);
      this.modal.appendChild(this.confirmButton);
    }
  }
  
  var warnBeforeLeave = new WarnBeforeLeave();
  warnBeforeLeave.init();
});