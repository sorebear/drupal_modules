function ToggleAdmin() {
  this.body = document.querySelector('body');
  this.buttonContainer = document.createElement('div');
  this.clearCacheButton = document.querySelector('a[data-drupal-link-system-path="admin/flush"]');
  this.dragging = false;
  this.bottom = 20;
  this.right = 20;

  this.init = function() {
    this.buttonContainer.classList.add('admin-control-buttons-container');
    this.body.appendChild(this.buttonContainer);

    this.createButtons();
    this.addKeyboardListeners();
    this.enableDragFunctionality();
  }

  this.addKeyboardListeners = function() {
    var that = this;
    document.addEventListener('keydown', function(e) {
      // Action on "alt + c"      
      if (e.keyCode === 67 && e.altKey) {
        that.clearCacheButton.click();
      }
    });
  }

  this.createButtons = function() {
    var that = this;
    this.createButton(['clear-cash'], ['fas', 'fa-trash'], 'Clear Cache', function() {
      that.clearCacheButton.click();
    });

    this.createButton(['admin-toggle'], ['fas', 'fa-bars'], 'Toggle Admin Menu', function() {
      that.body.classList.toggle('toggle-admin');
    });
  }

  this.createButton = function(classes, iconClasses, name, onClick) {
    var that = this;
    var button = document.createElement('button');
    button.classList.add('admin-control-button')
    button.name = name;
    
    for (var i = 0; i < classes.length; i += 1) {
      button.classList.add(classes[i]);
    }

    var icon = document.createElement('i');
    for (var j = 0; j < iconClasses.length; j += 1) {
      icon.classList.add(iconClasses[j]);
    }

    button.appendChild(icon);
    button.addEventListener('click', function() {
      if (that.dragging) {
        that.dragging = false;
      } else {
        onClick();
      }
    });
    this.buttonContainer.appendChild(button);
  }

  this.enableDragFunctionality = function() {
    var that = this;
    this.buttonContainer.addEventListener('mousemove', function(e) {
      if (e.buttons === 1) {        
        that.dragging = true;
        that.bottom -= e.movementY;
        that.right -= e.movementX;
        that.buttonContainer.style.bottom = that.bottom + 'px';
        that.buttonContainer.style.right = that.right + 'px';
      }
    })
  }
}


document.addEventListener('DOMContentLoaded', function() {
  var toggleAdmin = new ToggleAdmin();
  toggleAdmin.init();
});