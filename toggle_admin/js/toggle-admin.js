document.addEventListener('DOMContentLoaded', function() {
  var buttonContainer = document.createElement('div');
  buttonContainer.classList.add('admin-control-buttons-container');
  document.querySelector('body').appendChild(buttonContainer);

  function createButton(classes, iconClasses, name, onClick) {
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
    button.addEventListener('click', onClick);
    buttonContainer.appendChild(button);
  }

  createButton(['clear-cash'], ['fas', 'fa-trash'], 'Clear Cache', function() {
    var clearCacheButton = document.querySelector('a[data-drupal-link-system-path="admin/flush"]');
    clearCacheButton.click();
  });

  createButton(['admin-toggle'], ['fas', 'fa-bars'], 'Toggle Admin Menu', function() {
    document.querySelector('body').classList.toggle('toggle-admin');
  });
});