(function () {
  function $(selector, root) {
    return (root || document).querySelector(selector);
  }

  function $all(selector, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(selector));
  }

  function toggleNode(element, shouldShow) {
    if (!element) {
      return;
    }
    element.classList.toggle('hidden', !shouldShow);
  }

  document.addEventListener('DOMContentLoaded', function () {
    var container = $('.container[data-user-type]');
    var appHeader = $('.app-header');
    var menuToggle = $('[data-menu-toggle]');
    var headerRight = $('.header-right');
    var mobileNavDrawer = $('[data-mobile-nav-drawer]');
    var mobileNavBackdrop = $('[data-mobile-nav-backdrop]');
    var mobileNavClose = $('[data-mobile-nav-close]');
    var portalSidebar = $('.portal-layout .sidebar');
    var navGuest = $('[data-nav-guest]');
    var navCustomer = $('[data-nav-customer]');
    var navPortal = $('[data-nav-portal]');
    var modal = $('[data-login-modal]');
    var availabilityModal = $('[data-availability-modal]');
    var closeAvailabilityButtons = $all('[data-close-availability]');
    var form = $('[data-login-form]');
    var authActions = $('[data-auth-actions]');
    var accountShell = $('[data-account-shell]');
    var accountMenu = $('[data-account-menu]');
    var accountTrigger = $('[data-account-trigger]');
    var accountDropdown = $('[data-account-dropdown]');
    var accountPanelOption = $('[data-account-option-panel]');
    var accountOrdersOption = $('[data-account-option-orders]');
    var accountProfileOption = $('[data-account-option-profile]');
    var accountCart = $('[data-account-cart]');
    var storeSearch = $('[data-store-search]');
    var accountAvatar = $('[data-account-avatar]');
    var accountName = $('[data-account-name]');
    var accountRole = $('[data-account-role]');
    var closeButton = $('[data-close-login]');
    var logoutButton = $('[data-account-logout]');
    var googleLoginButton = $('[data-google-login]');
    var googleRegisterButton = $('[data-google-register]');
    var errorBox = $('[data-login-error]');
    var csrfTokenMeta = $('meta[name="csrf-token"]');
    var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
    var cartStorageKey = 'store_cart_v1';
    var portalModulesStorageKey = 'portal_modules_collapsed_v2';

    function closeMenu() {
      if (!appHeader || !menuToggle) {
        return;
      }
      appHeader.classList.remove('is-menu-open');
      menuToggle.setAttribute('aria-expanded', 'false');
      if (mobileNavDrawer) {
        mobileNavDrawer.classList.remove('is-open');
        mobileNavDrawer.setAttribute('aria-hidden', 'true');
      }
      if (portalSidebar) {
        portalSidebar.classList.remove('is-mobile-open');
      }
      if (mobileNavBackdrop) {
        mobileNavBackdrop.classList.remove('is-open');
        mobileNavBackdrop.setAttribute('aria-hidden', 'true');
      }
    }

    function toggleMenu() {
      if (!appHeader || !menuToggle) {
        return;
      }
      var open = appHeader.classList.toggle('is-menu-open');
      menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (mobileNavDrawer) {
        mobileNavDrawer.classList.toggle('is-open', open);
        mobileNavDrawer.setAttribute('aria-hidden', open ? 'false' : 'true');
      }
      if (portalSidebar && window.innerWidth <= 980) {
        portalSidebar.classList.toggle('is-mobile-open', open);
      }
      if (mobileNavBackdrop) {
        mobileNavBackdrop.classList.toggle('is-open', open);
        mobileNavBackdrop.setAttribute('aria-hidden', open ? 'false' : 'true');
      }
    }

    function closeAccountDropdown() {
      if (!accountDropdown || !accountTrigger) {
        return;
      }
      accountDropdown.classList.add('hidden');
      accountTrigger.setAttribute('aria-expanded', 'false');
    }

    function toggleAccountDropdown() {
      if (!accountDropdown || !accountTrigger) {
        return;
      }
      var isHidden = accountDropdown.classList.toggle('hidden');
      accountTrigger.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
    }

    function closeNavbarFloatingUI() {
      closeMenu();
      closeAccountDropdown();
    }

    function applyNavbarType(type) {
      var userType = type || 'guest';

      if (container) {
        container.setAttribute('data-user-type', userType);
      }

      toggleNode(navGuest, userType === 'guest');
      toggleNode(navCustomer, userType === 'customer');
      toggleNode(navPortal, userType === 'portal');
      toggleNode(authActions, userType === 'guest');
      toggleNode(accountShell, userType !== 'guest');
      toggleNode(storeSearch, userType !== 'portal');

      if (accountCart) {
        accountCart.classList.toggle('hidden', userType === 'portal');
      }

      if (accountPanelOption) {
        accountPanelOption.classList.toggle('hidden', userType !== 'portal');
      }

      if (accountOrdersOption) {
        accountOrdersOption.classList.toggle('hidden', userType === 'portal');
      }
    }

    function setBusy(isBusy) {
      var submitButton = form ? form.querySelector('button[type="submit"]') : null;
      if (submitButton) {
        submitButton.disabled = isBusy;
      }
      if (googleLoginButton) {
        googleLoginButton.disabled = isBusy;
      }
      if (googleRegisterButton) {
        googleRegisterButton.disabled = isBusy;
      }
    }

    function showError(message) {
      if (!errorBox) {
        return;
      }
      errorBox.textContent = message;
      errorBox.classList.remove('hidden');
    }

    function clearError() {
      if (!errorBox) {
        return;
      }
      errorBox.textContent = '';
      errorBox.classList.add('hidden');
    }

    function request(url, payload, method) {
      return fetch(url, {
        method: method || 'POST',
        headers: {
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Content-Type': 'application/json'
        },
        body: payload ? JSON.stringify(payload) : '{}'
      }).then(function (response) {
        return response.json().then(function (data) {
          return {
            ok: response.ok,
            status: response.status,
            data: data
          };
        });
      });
    }

    function applySessionUI(user) {
      if (!user) {
        applyNavbarType('guest');
        if (accountName) {
          accountName.textContent = '';
        }
        if (accountRole) {
          accountRole.textContent = '';
        }
        if (accountAvatar) {
          accountAvatar.removeAttribute('src');
        }
        closeAccountDropdown();
        return;
      }

      applyNavbarType(user.type || 'customer');
      if (accountAvatar) {
        accountAvatar.src = user.avatar;
        accountAvatar.alt = 'Foto de perfil de ' + user.name;
      }
      if (accountName) {
        accountName.textContent = user.name;
      }
      if (accountRole) {
        accountRole.textContent = user.roleLabel || 'Cliente de la tienda';
      }
      closeAccountDropdown();
    }

    function openModal() {
      closeNavbarFloatingUI();
      clearError();
      if (!modal) {
        return;
      }
      modal.classList.remove('hidden');
      modal.setAttribute('aria-hidden', 'false');
      window.setTimeout(function () {
        var emailInput = $('#login-email');
        if (emailInput) {
          emailInput.focus();
        }
      }, 0);
    }

    function closeModal() {
      if (!modal) {
        return;
      }
      modal.classList.add('hidden');
      modal.setAttribute('aria-hidden', 'true');
      clearError();
      if (form) {
        form.reset();
      }
    }

    function parsePriceNumber(value) {
      if (typeof value === 'number') {
        return value;
      }

      var sanitized = (value || '').toString().replace(/[^\d.]/g, '');
      var parsed = parseFloat(sanitized);
      return isNaN(parsed) ? 0 : parsed;
    }

    function formatPrice(value) {
      return 'MXN $' + parsePriceNumber(value).toFixed(2);
    }

    function readCart() {
      try {
        var raw = window.localStorage.getItem(cartStorageKey);
        if (!raw) {
          return [];
        }

        var parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : [];
      } catch (error) {
        return [];
      }
    }

    function syncLocalCartToServer() {
      if (!container || (container.getAttribute('data-user-type') || 'guest') === 'guest') {
        return Promise.resolve({ ok: true, data: {} });
      }

      var cartItems = readCart().filter(function (item) {
        return item && item.variant_id;
      }).map(function (item) {
        return {
          variant_id: parseInt(item.variant_id, 10),
          quantity: parseInt(item.quantity, 10) || 1
        };
      });

      if (!cartItems.length) {
        return Promise.resolve({ ok: true, data: {} });
      }

      return request('/api/cart/sync', {
        items: cartItems
      }, 'PUT').catch(function () {
        return { ok: false, data: { message: 'No se pudo sincronizar el carrito.' } };
      });
    }

    function writeCart(cartItems) {
      window.localStorage.setItem(cartStorageKey, JSON.stringify(cartItems));
    }

    function getCartTotalItems(cartItems) {
      return cartItems.reduce(function (total, item) {
        return total + (parseInt(item.quantity, 10) || 0);
      }, 0);
    }

    function updateCartBadges() {
      var totalItems = getCartTotalItems(readCart());
      $all('[data-cart-badge]').forEach(function (badge) {
        badge.textContent = String(totalItems);
        badge.classList.toggle('hidden', totalItems <= 0);
      });
    }

    function readWishlist() {
      try {
        var stored = window.localStorage.getItem('store_wishlist_v1');
        var parsed = stored ? JSON.parse(stored) : [];
        return Array.isArray(parsed) ? parsed : [];
      } catch (error) {
        return [];
      }
    }

    function writeWishlist(wishlist) {
      window.localStorage.setItem('store_wishlist_v1', JSON.stringify(wishlist));
    }

    function showShareToast(message) {
      var existingToast = $('[data-share-toast]');
      if (existingToast) {
        existingToast.remove();
      }

      var toast = document.createElement('div');
      toast.setAttribute('data-share-toast', '');
      toast.className = 'share-toast';
      toast.innerHTML = '<svg aria-hidden="true" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"></path></svg><span>' + message + '</span>';
      document.body.appendChild(toast);
      window.setTimeout(function () {
        toast.classList.add('is-visible');
      }, 20);
      window.setTimeout(function () {
        toast.classList.remove('is-visible');
        window.setTimeout(function () { toast.remove(); }, 220);
      }, 2400);
    }

    window.showToast = function (message, type, duration) {
      type = type || 'info';
      duration = duration || 5000;
      var container = document.getElementById('global-toast-container');
      if (container) {
        var toastItem = document.createElement('div');
        toastItem.className = 'toast-stack-item toast-' + type;
        toastItem.style.cssText = 'background: #0f172a; color: #fff; padding: 10px 16px; border-radius: 8px; font-size: 13px; font-weight: 500; display: flex; align-items: center; justify-content: space-between; gap: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); pointer-events: auto; transition: all 0.3s ease; transform: translateY(10px); opacity: 0; border: 1px solid rgba(255,255,255,0.1);';
        if (type === 'success') toastItem.style.borderColor = 'rgba(16,185,129,0.5)';
        if (type === 'error' || type === 'critical') toastItem.style.borderColor = 'rgba(239,68,68,0.5)';
        if (type === 'warning') toastItem.style.borderColor = 'rgba(245,158,11,0.5)';

        toastItem.innerHTML = '<span>' + message + '</span><button type="button" style="background:none; border:none; color:rgba(255,255,255,0.7); cursor:pointer; font-size:16px; font-weight:bold; line-height:1;" aria-label="Cerrar">&times;</button>';
        var closeBtn = toastItem.querySelector('button');
        closeBtn.addEventListener('click', function () {
          toastItem.style.opacity = '0';
          toastItem.style.transform = 'translateY(10px)';
          setTimeout(function () { toastItem.remove(); }, 300);
        });

        container.appendChild(toastItem);
        setTimeout(function () {
          toastItem.style.opacity = '1';
          toastItem.style.transform = 'translateY(0)';
        }, 20);

        setTimeout(function () {
          if (toastItem.parentElement) {
            toastItem.style.opacity = '0';
            toastItem.style.transform = 'translateY(10px)';
            setTimeout(function () { toastItem.remove(); }, 300);
          }
        }, duration);
      } else {
        showShareToast(message);
      }
    };

    function closeAvailabilityModal() {
      if (!availabilityModal) {
        return;
      }
      availabilityModal.classList.add('hidden');
      availabilityModal.setAttribute('aria-hidden', 'true');
    }

    function initPortalModulesSidebar() {
      var panel = $('[data-portal-modules-sidebar]');
      var toggle = $('[data-portal-modules-toggle]');
      var layout = $('[data-portal-layout]');
      if (!panel || !toggle) {
        return;
      }

      function setCollapsed(isCollapsed) {
        panel.classList.toggle('is-collapsed', isCollapsed);
        if (layout) {
          layout.classList.toggle('portal-sidebar-collapsed', isCollapsed);
        }
        toggle.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
        toggle.textContent = isCollapsed ? '>>' : '<<';
        toggle.setAttribute('title', isCollapsed ? 'Expandir panel de modulos' : 'Contraer panel de modulos');
        window.localStorage.setItem(portalModulesStorageKey, isCollapsed ? '1' : '0');
      }

      setCollapsed(window.localStorage.getItem(portalModulesStorageKey) === '1');

      toggle.addEventListener('click', function () {
        setCollapsed(!panel.classList.contains('is-collapsed'));
      });
    }

    function openAvailabilityModal() {
      closeNavbarFloatingUI();
      if (!availabilityModal) {
        return;
      }
      availabilityModal.classList.remove('hidden');
      availabilityModal.setAttribute('aria-hidden', 'false');
    }

    function selectedCardSize(card) {
      var activeButton = $('[data-size-option].is-active', card);
      if (activeButton) {
        return (activeButton.getAttribute('data-size-option') || 'U').trim() || 'U';
      }
      return 'U';
    }

    function selectedCardColor(card) {
      var activeButton = $('[data-color-option].is-active', card);
      return activeButton ? (activeButton.getAttribute('data-color-option') || 'Unico').trim() : 'Unico';
    }

    function resolveSelectedVariant(card) {
      var rawMap = card.getAttribute('data-variant-map') || '[]';
      var variants;
      try {
        variants = JSON.parse(rawMap);
      } catch (error) {
        variants = [];
      }

      var selected = variants.find(function (variant) {
        return variant.size === selectedCardSize(card) && variant.color === selectedCardColor(card);
      }) || variants.find(function (variant) {
        return variant.size === selectedCardSize(card);
      }) || variants[0];

      if (selected) {
        card.setAttribute('data-product-variant-id', selected.id);
        card.setAttribute('data-product-price', selected.price);
      }

      return selected || null;
    }

    function selectedCardQuantity(card) {
      var qtyInput = $('[data-qty-input]', card);
      var qty = qtyInput ? parseInt(qtyInput.value || '1', 10) : 1;
      if (isNaN(qty) || qty < 1) {
        qty = 1;
      }
      return qty;
    }

    function addCardToCart(card) {
      var name = card.getAttribute('data-product-name') || 'Producto';
      var slug = card.getAttribute('data-product-slug') || name.toLowerCase().replace(/\s+/g, '-');
      var size = selectedCardSize(card);
      var quantity = selectedCardQuantity(card);
      var price = parsePriceNumber(card.getAttribute('data-product-price') || '0');
      var image = card.getAttribute('data-product-image') || '';
      var description = card.getAttribute('data-product-description') || '';
      var selectedVariant = resolveSelectedVariant(card);
      var variantId = selectedVariant ? selectedVariant.id : (card.getAttribute('data-product-variant-id') || '');
      if (selectedVariant) {
        price = parsePriceNumber(selectedVariant.price);
      }
      var cartItems = readCart();
      var lineKey = slug + '::' + variantId;
      var existingIndex = cartItems.findIndex(function (item) {
        return item.lineKey === lineKey;
      });

      if (existingIndex > -1) {
        cartItems[existingIndex].quantity = (parseInt(cartItems[existingIndex].quantity, 10) || 0) + quantity;
      } else {
        cartItems.push({
          lineKey: lineKey,
          slug: slug,
          name: name,
          size: size,
          quantity: quantity,
          price: price,
          image: image,
          description: description,
          variant_id: variantId
        });
      }

      writeCart(cartItems);
      updateCartBadges();
      syncLocalCartToServer();
    }

    function decorateProductCard(card) {
      if (card.getAttribute('data-card-decorated') === 'true') {
        return;
      }

      var media = $('.product-media', card);
      var controls = $('.product-purchase-controls', card);
      if (!media || !controls) {
        return;
      }

      var category = card.getAttribute('data-product-category') || card.getAttribute('data-garment-type') || 'Prenda';
      var color = card.getAttribute('data-product-color') || card.getAttribute('data-color') || '';
      var categoryLabel = document.createElement('span');
      categoryLabel.className = 'product-card-category';
      categoryLabel.textContent = category;
      media.insertAdjacentElement('afterend', categoryLabel);
      var cardActions = document.createElement('div');
      cardActions.className = 'product-card-image-actions';
      cardActions.innerHTML = '<button type="button" class="product-card-icon-button" aria-label="Agregar a favoritos" title="Favoritos"><svg aria-hidden="true" viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.8 8.8c0 5.2-8.8 10.1-8.8 10.1S3.2 14 3.2 8.8A4.8 4.8 0 0 1 12 6.2a4.8 4.8 0 0 1 8.8 2.6Z"></path></svg></button><button type="button" class="product-card-icon-button" aria-label="Compartir producto" title="Compartir"><svg aria-hidden="true" viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="2.5"></circle><circle cx="6" cy="12" r="2.5"></circle><circle cx="18" cy="19" r="2.5"></circle><path d="m8.2 10.8 7.6-4.6M8.2 13.2l7.6 4.6"></path></svg></button>';
      media.appendChild(cardActions);

      if (color) {
        var colorLabel = document.createElement('span');
        colorLabel.className = 'product-card-color';
        colorLabel.textContent = 'Color: ' + color;
        categoryLabel.insertAdjacentElement('afterend', colorLabel);
      }

      var sizeHeader = document.createElement('div');
      sizeHeader.className = 'product-card-size-header';
      sizeHeader.innerHTML = '<span>Talla:</span><a href="/productos#guia-tallas">Guia de tallas</a>';
      controls.insertAdjacentElement('beforebegin', sizeHeader);
      card.setAttribute('data-card-decorated', 'true');

      var productKey = card.getAttribute('data-product-variant-id') || card.getAttribute('data-product-slug') || card.getAttribute('data-product-name');
      var favoriteButton = cardActions.querySelector('[aria-label="Agregar a favoritos"]');
      var shareButton = cardActions.querySelector('[aria-label="Compartir producto"]');
      var wishlist = readWishlist();
      if (wishlist.indexOf(productKey) !== -1 && favoriteButton) {
        favoriteButton.classList.add('is-active');
        favoriteButton.setAttribute('aria-pressed', 'true');
      }

      if (favoriteButton) {
        favoriteButton.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();
          var currentWishlist = readWishlist();
          var index = currentWishlist.indexOf(productKey);
          if (index === -1) {
            currentWishlist.push(productKey);
            favoriteButton.classList.add('is-active');
            favoriteButton.setAttribute('aria-pressed', 'true');
          } else {
            currentWishlist.splice(index, 1);
            favoriteButton.classList.remove('is-active');
            favoriteButton.setAttribute('aria-pressed', 'false');
          }
          writeWishlist(currentWishlist);
        });
      }

      if (shareButton) {
        shareButton.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();
          var shareUrl = window.location.origin + '/productos/' + encodeURIComponent(card.getAttribute('data-product-slug') || '');
          var shareData = { title: card.getAttribute('data-product-name') || 'Producto', text: 'Descubre este producto en Mi Tienda.', url: shareUrl };
          if (navigator.share) {
            navigator.share(shareData).catch(function () {});
            return;
          }
          if (navigator.clipboard) {
            navigator.clipboard.writeText(shareUrl).then(function () {
              showShareToast('Enlace copiado al portapapeles.');
            });
          }
        });
      }
    }

    function attachProductCardInteractions() {
      var cards = $all('[data-product-card]');

      cards.forEach(function (card) {
        decorateProductCard(card);
        var sizeButtons = $all('[data-size-option]', card);
        var qtyInput = $('[data-qty-input]', card);
        var decreaseButton = $('[data-qty-decrease]', card);
        var increaseButton = $('[data-qty-increase]', card);
        var addButton = $('[data-add-to-cart]', card);
        var availabilityButton = $('[data-request-availability]', card);

        sizeButtons.forEach(function (button) {
          button.addEventListener('click', function () {
            sizeButtons.forEach(function (candidate) {
              candidate.classList.remove('is-active');
            });
            button.classList.add('is-active');
          });
        });

        var colorButtons = $all('[data-color-option]', card);
        colorButtons.forEach(function (button) {
          button.addEventListener('click', function () {
            colorButtons.forEach(function (candidate) {
              candidate.classList.remove('is-active');
            });
            button.classList.add('is-active');
            resolveSelectedVariant(card);
          });
        });

        sizeButtons.forEach(function (button) {
          button.addEventListener('click', function () {
            resolveSelectedVariant(card);
          });
        });

        if (decreaseButton && qtyInput) {
          decreaseButton.addEventListener('click', function () {
            var current = parseInt(qtyInput.value || '1', 10);
            if (isNaN(current) || current <= 1) {
              qtyInput.value = '1';
              return;
            }
            qtyInput.value = String(current - 1);
          });
        }

        if (increaseButton && qtyInput) {
          increaseButton.addEventListener('click', function () {
            var current = parseInt(qtyInput.value || '1', 10);
            if (isNaN(current) || current < 1) {
              current = 1;
            }
            qtyInput.value = String(current + 1);
          });
        }

        if (qtyInput) {
          qtyInput.addEventListener('change', function () {
            var current = parseInt(qtyInput.value || '1', 10);
            if (isNaN(current) || current < 1) {
              qtyInput.value = '1';
            }
          });
        }

        if (addButton) {
          addButton.setAttribute('data-original-label', addButton.textContent);
          addButton.addEventListener('click', function () {
            addCardToCart(card);
            addButton.textContent = 'Agregado';
            window.setTimeout(function () {
              addButton.textContent = addButton.getAttribute('data-original-label') || 'Agregar';
            }, 1200);
          });
        }

        if (availabilityButton) {
          availabilityButton.addEventListener('click', openAvailabilityModal);
        }
      });
    }

    function renderCartPage() {
      var cartPage = $('[data-cart-page]');
      if (!cartPage) {
        return;
      }

      var cartItemsContainer = $('[data-cart-items]', cartPage);
      var emptyState = $('[data-cart-empty]', cartPage);
      var summary = $('[data-cart-summary]', cartPage);
      var itemsLabel = $('[data-cart-total-items]', cartPage);
      var subtotalLabel = $('[data-cart-subtotal]', cartPage);
      var totalLabel = $('[data-cart-total]', cartPage);
      var continueButton = $('[data-checkout-continue]', cartPage);
      var itemsCountLabel = $('[data-cart-items-count]', cartPage);
      var freeShippingMessage = $('[data-free-shipping-message]', cartPage);
      var freeShippingFill = $('[data-free-shipping-fill]', cartPage);
      var clearCartButton = $('[data-cart-clear]', cartPage);

      function paint() {
        var cartItems = readCart();
        var totalItems = getCartTotalItems(cartItems);
        var subtotal = cartItems.reduce(function (total, item) {
          var qty = parseInt(item.quantity, 10) || 0;
          return total + (parsePriceNumber(item.price) * qty);
        }, 0);
        var freeShippingThreshold = 999;
        var freeShippingProgress = Math.min(100, (subtotal / freeShippingThreshold) * 100);
        var remainingForFreeShipping = Math.max(0, freeShippingThreshold - subtotal);

        if (cartItemsContainer) {
          cartItemsContainer.innerHTML = cartItems.map(function (item, index) {
            return '' +
              '<article class="cart-item" data-cart-line="' + index + '">' +
              '<img src="' + (item.image || 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=1200&q=80') + '" alt="' + item.name + '" loading="lazy">' +
              '<div class="cart-item-copy">' +
              '<h3>' + item.name + '</h3>' +
              '<p class="cart-item-meta">Talla ' + item.size + ' · ' + formatPrice(item.price) + '</p>' +
              '<div class="cart-item-actions">' +
              '<div class="qty-selector" data-cart-qty>' +
              '<button type="button" data-cart-minus="' + index + '">-</button>' +
              '<input type="number" min="1" value="' + item.quantity + '" data-cart-qty-input="' + index + '">' +
              '<button type="button" data-cart-plus="' + index + '">+</button>' +
              '</div>' +
              '<button type="button" class="btn ghost cart-remove" data-cart-remove="' + index + '">Eliminar</button>' +
              '</div>' +
              '</div>' +
              '</article>';
          }).join('');
        }

        if (itemsLabel) {
          itemsLabel.textContent = String(totalItems);
        }
        if (itemsCountLabel) {
          itemsCountLabel.textContent = totalItems + (totalItems === 1 ? ' prenda' : ' prendas');
        }
        if (freeShippingMessage) {
          freeShippingMessage.textContent = remainingForFreeShipping > 0
            ? 'Agrega ' + formatPrice(remainingForFreeShipping) + ' para envio gratis'
            : 'Envio gratis desbloqueado';
        }
        if (freeShippingFill) {
          freeShippingFill.style.width = freeShippingProgress + '%';
        }
        if (subtotalLabel) {
          subtotalLabel.textContent = formatPrice(subtotal);
        }
        if (totalLabel) {
          totalLabel.textContent = formatPrice(subtotal);
        }

        if (emptyState) {
          emptyState.classList.toggle('hidden', totalItems > 0);
        }
        if (summary) {
          summary.classList.toggle('hidden', totalItems <= 0);
        }
        if (continueButton) {
          continueButton.classList.toggle('hidden', totalItems <= 0);
          continueButton.setAttribute('aria-disabled', totalItems <= 0 ? 'true' : 'false');
        }

        updateCartBadges();
      }

      if (cartItemsContainer) {
        cartItemsContainer.addEventListener('click', function (event) {
          var removeButton = event.target.closest('[data-cart-remove]');
          var minusButton = event.target.closest('[data-cart-minus]');
          var plusButton = event.target.closest('[data-cart-plus]');
          var cartItems = readCart();

          if (removeButton) {
            var removeIndex = parseInt(removeButton.getAttribute('data-cart-remove') || '-1', 10);
            if (removeIndex > -1) {
              cartItems.splice(removeIndex, 1);
              writeCart(cartItems);
              paint();
            }
            return;
          }

          if (minusButton) {
            var minusIndex = parseInt(minusButton.getAttribute('data-cart-minus') || '-1', 10);
            if (minusIndex > -1 && cartItems[minusIndex]) {
              cartItems[minusIndex].quantity = Math.max(1, (parseInt(cartItems[minusIndex].quantity, 10) || 1) - 1);
              writeCart(cartItems);
              paint();
            }
            return;
          }

          if (plusButton) {
            var plusIndex = parseInt(plusButton.getAttribute('data-cart-plus') || '-1', 10);
            if (plusIndex > -1 && cartItems[plusIndex]) {
              cartItems[plusIndex].quantity = (parseInt(cartItems[plusIndex].quantity, 10) || 1) + 1;
              writeCart(cartItems);
              paint();
            }
          }
        });

        cartItemsContainer.addEventListener('change', function (event) {
          var qtyInput = event.target.closest('[data-cart-qty-input]');
          if (!qtyInput) {
            return;
          }

          var cartItems = readCart();
          var index = parseInt(qtyInput.getAttribute('data-cart-qty-input') || '-1', 10);
          if (index < 0 || !cartItems[index]) {
            return;
          }

          var nextQty = parseInt(qtyInput.value || '1', 10);
          if (isNaN(nextQty) || nextQty < 1) {
            nextQty = 1;
          }

          cartItems[index].quantity = nextQty;
          writeCart(cartItems);
          paint();
        });
      }

      if (clearCartButton) {
        clearCartButton.addEventListener('click', function () {
          if (!readCart().length || !window.confirm('¿Quieres vaciar tu carrito?')) {
            return;
          }
          writeCart([]);
          paint();
        });
      }

      paint();
    }

    function escapeCheckoutHtml(value) {
      return (value || '').toString().replace(/[&<>'"]/g, function (character) {
        return {
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          "'": '&#039;',
          '"': '&quot;'
        }[character];
      });
    }

    function renderCheckoutSummaries() {
      $all('[data-checkout-summary]').forEach(function (checkoutSummary) {
        var cartItems = readCart();
        if (!cartItems.length) {
          checkoutSummary.innerHTML = '<p class="muted">Tu carrito esta vacio.</p>';
          return;
        }

        var total = cartItems.reduce(function (sum, item) {
          return sum + parsePriceNumber(item.price) * (parseInt(item.quantity, 10) || 0);
        }, 0);
        checkoutSummary.innerHTML = cartItems.map(function (item) {
          var quantity = parseInt(item.quantity, 10) || 0;
          var lineTotal = parsePriceNumber(item.price) * quantity;
          return '<div class="checkout-summary-line"><span>' + escapeCheckoutHtml(item.name || 'Producto') + ' x' + quantity + '</span><strong>' + formatPrice(lineTotal) + '</strong></div>';
        }).join('') + '<p class="checkout-summary-total">Total: ' + formatPrice(total) + '</p>';
      });
    }

    function initCheckoutPage() {
      var checkoutForm = $('[data-checkout-form]');
      if (!checkoutForm) {
        return;
      }

      var submitButton = $('[data-checkout-submit]', checkoutForm);
      var errorBox = $('[data-checkout-error]', checkoutForm);
      var successBox = $('[data-checkout-success]', checkoutForm);
      var postalCodeInput = $('[data-postal-code]', checkoutForm);
      var postalStatus = $('[data-postal-status]', checkoutForm);
      var neighborhoodInput = $('[data-postal-neighborhood]', checkoutForm);
      var cityInput = $('[data-postal-city]', checkoutForm);
      var stateInput = $('[data-postal-state]', checkoutForm);
      var quoteButton = $('[data-quote-shipping]', checkoutForm);
      var quoteStatus = $('[data-quote-shipping-status]', checkoutForm);
      var quoteResults = $('[data-quote-shipping-results]', checkoutForm);
      var deliveryMethodInput = $('[data-delivery-method-input]', checkoutForm);
      var deliveryTabs = $all('[data-delivery-tab]', checkoutForm);
      var personalDeliveryPanel = $('[data-personal-delivery-panel]', checkoutForm);
      function selectDeliveryTab(method) {
        if (deliveryMethodInput) {
          deliveryMethodInput.value = method;
        }
        deliveryTabs.forEach(function (tab) {
          var isSelected = tab.getAttribute('data-delivery-tab') === method;
          tab.setAttribute('aria-selected', isSelected ? 'true' : 'false');
        });
        if (quotePanel) {
          quotePanel.classList.toggle('hidden', method !== 'paqueteria');
        }
        if (personalDeliveryPanel) {
          personalDeliveryPanel.classList.toggle('hidden', method !== 'personal');
        }
      }

      function setPostalStatus(message) {
        if (postalStatus) {
          postalStatus.textContent = message;
        }
      }

      function lookupPostalCode() {
        var postalCode = postalCodeInput ? postalCodeInput.value.trim() : '';
        if (!/^\d{5}$/.test(postalCode)) {
          setPostalStatus('');
          return;
        }

        setPostalStatus('Buscando ubicacion...');
        fetch('https://api.zippopotam.us/mx/' + postalCode, {
          headers: { Accept: 'application/json' }
        }).then(function (response) {
          if (!response.ok) {
            throw new Error('Codigo postal no encontrado');
          }
          return response.json();
        }).then(function (data) {
          var place = data.places && data.places[0] ? data.places[0] : null;
          if (!place) {
            throw new Error('Codigo postal no encontrado');
          }
          if (cityInput && !cityInput.value) {
            cityInput.value = place['place name'] || '';
          }
          if (stateInput && !stateInput.value) {
            stateInput.value = place['state'] || '';
          }
          setPostalStatus('Ciudad y estado encontrados. Completa o verifica la colonia.');
        }).catch(function () {
          setPostalStatus('No encontramos el codigo postal; completa la ubicacion manualmente.');
        });
      }

      if (postalCodeInput) {
        postalCodeInput.addEventListener('blur', lookupPostalCode);
        postalCodeInput.addEventListener('input', function () {
          if (postalCodeInput.value.length < 5) {
            setPostalStatus('');
          }
        });
      }

      var quotePanel = $('[data-shipping-quote-panel]', checkoutForm);
      deliveryTabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
          selectDeliveryTab(tab.getAttribute('data-delivery-tab') || '');
        });
      });

      if (quoteButton) {
        quoteButton.addEventListener('click', function () {
          var postalCode = postalCodeInput ? postalCodeInput.value.trim() : '';
          if (!/^\d{5}$/.test(postalCode)) {
            setPostalStatus('Escribe un codigo postal valido antes de cotizar.');
            if (postalCodeInput) {
              postalCodeInput.focus();
            }
            return;
          }

          quoteButton.disabled = true;
          quoteButton.textContent = 'Cotizando...';
          if (quoteStatus) {
            quoteStatus.textContent = 'La consulta de tarifas quedara conectada a SkyDrop.';
          }
          if (quoteResults) {
            quoteResults.innerHTML = '';
          }
          window.setTimeout(function () {
            quoteButton.disabled = false;
            quoteButton.textContent = 'Cotizar envio';
            if (quoteStatus) {
              quoteStatus.textContent = 'No hay tarifas disponibles hasta configurar la conexion con SkyDrop.';
            }
          }, 500);
        });
      }

      function showCheckoutMessage(box, message) {
        if (!box) {
          return;
        }
        box.textContent = message;
        box.classList.remove('hidden');
      }

      function clearCheckoutMessages() {
        [errorBox, successBox].forEach(function (box) {
          if (box) {
            box.textContent = '';
            box.classList.add('hidden');
          }
        });
      }

      function syncLocalCart() {
        var cartItems = readCart();
        if (!cartItems.length) {
          return Promise.resolve({ ok: true, data: {} });
        }

        var missingVariant = cartItems.some(function (item) {
          return !item.variant_id;
        });
        if (missingVariant) {
          return Promise.reject(new Error('Actualiza tu carrito desde el catalogo antes de confirmar el pedido.'));
        }

        return request('/api/cart/sync', {
          items: cartItems.map(function (item) {
            return {
              variant_id: parseInt(item.variant_id, 10),
              quantity: parseInt(item.quantity, 10) || 1
            };
          })
        }, 'PUT').then(function (result) {
          if (!result.ok) {
            throw new Error('No se pudo sincronizar el carrito.');
          }
          return result;
        });
      }

      checkoutForm.addEventListener('submit', function (event) {
        event.preventDefault();
        clearCheckoutMessages();
        if (!deliveryMethodInput || !deliveryMethodInput.value) {
          showCheckoutMessage(errorBox, 'Selecciona un metodo de entrega para continuar.');
          if (deliveryTabs[0]) {
            deliveryTabs[0].focus();
          }
          return;
        }
        if (submitButton) {
          submitButton.disabled = true;
          submitButton.textContent = 'Procesando...';
        }

        var formData = new FormData(checkoutForm);
        syncLocalCart().then(function () {
          return request('/checkout', {
            name: (formData.get('name') || '').toString().trim(),
            email: (formData.get('email') || '').toString().trim(),
            mobile: (formData.get('mobile') || '').toString().trim(),
            birth_date: (formData.get('birth_date') || '').toString(),
            street: (formData.get('street') || '').toString().trim(),
            exterior_number: (formData.get('exterior_number') || '').toString().trim(),
            interior_number: (formData.get('interior_number') || '').toString().trim(),
            neighborhood: (formData.get('neighborhood') || '').toString().trim(),
            postal_code: (formData.get('postal_code') || '').toString().trim(),
            city: (formData.get('city') || '').toString().trim(),
            state: (formData.get('state') || '').toString().trim(),
            delivery_references: (formData.get('delivery_references') || '').toString().trim(),
            delivery_method: (formData.get('delivery_method') || '').toString()
          });
        }).then(function (result) {
          if (!result.ok) {
            var errors = result.data && result.data.errors;
            var message = errors && errors.cart ? errors.cart[0] : null;
            showCheckoutMessage(errorBox, message || (result.data && result.data.message) || 'No se pudo crear el pedido.');
            return;
          }

          var orderNumber = result.data && result.data.order ? result.data.order.order_number : '';
          if (!orderNumber) {
            throw new Error('El pedido fue creado, pero no recibimos su numero de confirmacion.');
          }

          checkoutForm.reset();
          writeCart([]);
          updateCartBadges();
          window.location.href = '/checkout/exito?order=' + encodeURIComponent(orderNumber);
        }).catch(function (error) {
          showCheckoutMessage(errorBox, error.message || 'Error de red. Intenta nuevamente.');
        }).finally(function () {
          if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = 'Confirmar pedido';
          }
        });
      });
    }

    if (appHeader && menuToggle && headerRight) {
      menuToggle.addEventListener('click', toggleMenu);

      if (mobileNavClose) {
        mobileNavClose.addEventListener('click', closeMenu);
      }
      if (mobileNavBackdrop) {
        mobileNavBackdrop.addEventListener('click', closeMenu);
      }

      headerRight.addEventListener('click', function (event) {
        var target = event.target;
        if (target && (target.tagName === 'A' || (target.closest && target.closest('[data-account-logout]')))) {
          closeMenu();
        }
      });

      window.addEventListener('resize', function () {
        if (window.innerWidth > 980) {
          closeMenu();
        }
      });

      document.addEventListener('click', function (event) {
        if (!appHeader.contains(event.target)) {
          closeMenu();
        }
      });
    }

    if (accountTrigger && accountMenu && accountDropdown) {
      accountTrigger.addEventListener('click', toggleAccountDropdown);

      document.addEventListener('click', function (event) {
        if (!accountMenu.contains(event.target)) {
          closeAccountDropdown();
        }
      });
    }

    if (modal && form) {
      $all('[data-open-login]').forEach(function (button) {
        button.addEventListener('click', openModal);
      });

      if (closeButton) {
        closeButton.addEventListener('click', closeModal);
      }

      modal.addEventListener('click', function (event) {
        if (event.target === modal) {
          closeModal();
        }
      });

      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
          if (modal && !modal.classList.contains('hidden')) {
            closeModal();
          }
          if (availabilityModal && !availabilityModal.classList.contains('hidden')) {
            closeAvailabilityModal();
          }
          closeNavbarFloatingUI();
        }
      });

      [accountProfileOption, accountOrdersOption, accountPanelOption, accountCart].forEach(function (link) {
        if (!link) {
          return;
        }
        link.addEventListener('click', closeNavbarFloatingUI);
      });

      form.addEventListener('submit', function (event) {
        event.preventDefault();
        var formData = new FormData(form);
        var email = (formData.get('email') || '').toString().trim();
        var password = (formData.get('password') || '').toString().trim();

        if (!email || !password) {
          showError('Completa correo y contrasena para continuar.');
          return;
        }

        setBusy(true);
        clearError();

        request('/auth/login', {
          email: email,
          password: password
        }).then(function (result) {
          if (!result.ok) {
            showError(result.data && result.data.message ? result.data.message : 'No se pudo iniciar sesion.');
            return;
          }
          applySessionUI(result.data.user || null);
          closeModal();
        }).catch(function () {
          showError('Error de red. Intenta nuevamente.');
        }).finally(function () {
          setBusy(false);
        });
      });
    }

    if (logoutButton) {
      logoutButton.addEventListener('click', function () {
        request('/auth/logout').then(function () {
          applySessionUI(null);
          window.location.href = '/';
        });
      });
    }

    applyNavbarType(container ? container.getAttribute('data-user-type') : 'guest');
    updateCartBadges();
    syncLocalCartToServer();
    attachProductCardInteractions();
    renderCartPage();
    renderCheckoutSummaries();
    initCheckoutPage();
    initPortalModulesSidebar();

    if (availabilityModal) {
      closeAvailabilityButtons.forEach(function (button) {
        button.addEventListener('click', closeAvailabilityModal);
      });

      availabilityModal.addEventListener('click', function (event) {
        if (event.target === availabilityModal) {
          closeAvailabilityModal();
        }
      });
    }

    var catalogFilters = $('[data-catalog-filters]');
    if (catalogFilters) {
      var filterSearch = $('[data-filter-search]', catalogFilters);
      var filterSort = $('[data-filter-sort]', catalogFilters);
      var filterReset = $('[data-filter-reset]', catalogFilters);
      var sourceButtons = $all('[data-filter-source]', catalogFilters);
      var sourceDropdown = $('[data-filter-source-toggle]', catalogFilters);
      var sourceTrigger = $('[data-filter-source-trigger]', catalogFilters);
      var sourceLabel = $('[data-filter-source-label]', catalogFilters);
      var perPageButtons = $all('[data-per-page]', catalogFilters);
      var catalogCards = $all('[data-product-card]');
      var catalogGrid = $('[data-catalog-grid]');
      var filterCheckboxes = $all('[data-filter-group]', catalogFilters);
      var resultsCount = $('[data-results-count]');
      var resultsEmpty = $('[data-results-empty]');
      var pagination = $('[data-catalog-pagination]');
      var prevPageButton = $('[data-page-prev]');
      var nextPageButton = $('[data-page-next]');
      var pageStatus = $('[data-page-status]');

      var filterKeys = ['brand', 'size', 'fit', 'color', 'category', 'garment_type'];
      var currentSourceFilter = 'all';
      var currentPerPage = 25;
      var currentPage = 1;

      function normalize(value) {
        return (value || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
      }

      function setSourceFilter(source) {
        var allowedSources = ['all', 'stock', 'extended', 'preorder', 'outlet'];
        var sourceLabels = {
          all: 'Todo',
          stock: 'En stock',
          extended: 'Catalogo extendido',
          preorder: 'Preventas Exclusivas',
          outlet: 'Outlet'
        };
        currentSourceFilter = allowedSources.indexOf(source) > -1 ? source : 'all';
        if (sourceLabel) {
          sourceLabel.textContent = sourceLabels[currentSourceFilter] || 'Todo';
        }
        sourceButtons.forEach(function (button) {
          var isActive = button.getAttribute('data-filter-source') === currentSourceFilter;
          button.classList.toggle('is-active', isActive);
        });
        if (sourceTrigger) {
          sourceTrigger.setAttribute('aria-expanded', sourceDropdown && sourceDropdown.hasAttribute('open') ? 'true' : 'false');
        }
      }

      function setPerPage(value) {
        var parsed = parseInt(value, 10);
        if ([25, 50, 100].indexOf(parsed) === -1) {
          parsed = 25;
        }
        currentPerPage = parsed;
        perPageButtons.forEach(function (button) {
          var isActive = parseInt(button.getAttribute('data-per-page') || '25', 10) === currentPerPage;
          button.classList.toggle('is-active', isActive);
        });
      }

      function setCurrentPage(value) {
        var parsed = parseInt(value, 10);
        if (isNaN(parsed) || parsed < 1) {
          parsed = 1;
        }
        currentPage = parsed;
      }

      function selectedValuesFor(group) {
        return filterCheckboxes
          .filter(function (checkbox) {
            return checkbox.getAttribute('data-filter-group') === group && checkbox.checked;
          })
          .map(function (checkbox) {
            return normalize(checkbox.value);
          });
      }

      function readFiltersFromUrl() {
        var params = new URLSearchParams(window.location.search || '');

        if (filterSearch) {
          filterSearch.value = params.get('q') || '';
        }

        if (filterSort) {
          filterSort.value = params.get('sort') || 'relevance';
        }

        setSourceFilter(params.get('source') || 'all');
        setPerPage(params.get('per_page') || '25');
        setCurrentPage(params.get('page') || '1');

        filterKeys.forEach(function (key) {
          var raw = params.get(key);
          var values = raw ? raw.split(',').map(normalize).filter(Boolean) : [];

          filterCheckboxes.forEach(function (checkbox) {
            if (checkbox.getAttribute('data-filter-group') !== key) {
              return;
            }
            checkbox.checked = values.indexOf(normalize(checkbox.value)) > -1;
          });
        });
      }

      function syncUrlWithFilters() {
        var params = new URLSearchParams();

        if (filterSearch && filterSearch.value.trim()) {
          params.set('q', filterSearch.value.trim());
        }

        if (filterSort && filterSort.value && filterSort.value !== 'relevance') {
          params.set('sort', filterSort.value);
        }

        if (currentSourceFilter !== 'all') {
          params.set('source', currentSourceFilter);
        }

        if (currentPerPage !== 25) {
          params.set('per_page', String(currentPerPage));
        }

        if (currentPage > 1) {
          params.set('page', String(currentPage));
        }

        filterKeys.forEach(function (key) {
          var values = selectedValuesFor(key);
          if (values.length) {
            params.set(key, values.join(','));
          }
        });

        var query = params.toString();
        var nextUrl = window.location.pathname + (query ? ('?' + query) : '');
        window.history.replaceState({}, '', nextUrl);
      }

      function cardMatches(card, query, filters) {
        var cardSource = normalize(card.getAttribute('data-source') || 'stock');
        if (currentSourceFilter !== 'all' && cardSource !== currentSourceFilter) {
          return false;
        }

        var sourceText = normalize(card.getAttribute('data-search'));
        if (query && sourceText.indexOf(query) === -1) {
          return false;
        }

        if (filters.brand.length && filters.brand.indexOf(normalize(card.getAttribute('data-brand'))) === -1) {
          return false;
        }
        if (filters.size.length && filters.size.indexOf(normalize(card.getAttribute('data-size'))) === -1) {
          return false;
        }
        if (filters.fit.length && filters.fit.indexOf(normalize(card.getAttribute('data-fit'))) === -1) {
          return false;
        }
        if (filters.color.length && filters.color.indexOf(normalize(card.getAttribute('data-color'))) === -1) {
          return false;
        }
        if (filters.category.length && filters.category.indexOf(normalize(card.getAttribute('data-category'))) === -1) {
          return false;
        }
        if (filters.garment_type.length && filters.garment_type.indexOf(normalize(card.getAttribute('data-garment-type'))) === -1) {
          return false;
        }

        return true;
      }

      function cardSortValue(card, sortKey) {
        if (sortKey === 'precio_asc' || sortKey === 'precio_desc') {
          return parseFloat(card.getAttribute('data-price') || '0');
        }

        if (sortKey === 'nombre_asc' || sortKey === 'nombre_desc') {
          return normalize(card.getAttribute('data-name'));
        }

        return parseInt(card.getAttribute('data-index') || '0', 10);
      }

      function sortVisibleCards(cards, sortKey) {
        var sorted = cards.slice();

        sorted.sort(function (a, b) {
          var aValue = cardSortValue(a, sortKey);
          var bValue = cardSortValue(b, sortKey);

          if (sortKey === 'precio_desc' || sortKey === 'nombre_desc') {
            if (aValue < bValue) {
              return 1;
            }
            if (aValue > bValue) {
              return -1;
            }
            return 0;
          }

          if (aValue > bValue) {
            return 1;
          }
          if (aValue < bValue) {
            return -1;
          }
          return 0;
        });

        return sorted;
      }

      function updateCatalogResults(shouldSyncUrl, keepPage) {
        var query = normalize(filterSearch ? filterSearch.value : '');
        var filters = {
          brand: selectedValuesFor('brand'),
          size: selectedValuesFor('size'),
          fit: selectedValuesFor('fit'),
          color: selectedValuesFor('color'),
          category: selectedValuesFor('category'),
          garment_type: selectedValuesFor('garment_type')
        };

        var sortKey = filterSort ? filterSort.value : 'relevance';

        if (!keepPage) {
          currentPage = 1;
        }

        var visibleCards = [];
        catalogCards.forEach(function (card) {
          if (cardMatches(card, query, filters)) {
            visibleCards.push(card);
          }
        });

        var sortedVisibleCards = sortVisibleCards(visibleCards, sortKey);
        var totalResults = sortedVisibleCards.length;
        var totalPages = Math.max(1, Math.ceil(totalResults / currentPerPage));

        if (currentPage > totalPages) {
          currentPage = totalPages;
        }

        var startIndex = (currentPage - 1) * currentPerPage;
        var endIndex = startIndex + currentPerPage;

        catalogCards.forEach(function (card) {
          card.classList.add('hidden');
        });

        if (catalogGrid) {
          sortedVisibleCards.forEach(function (card, index) {
            catalogGrid.appendChild(card);
            card.classList.toggle('hidden', !(index >= startIndex && index < endIndex));
          });
        }

        if (resultsCount) {
          resultsCount.textContent = totalResults + ' resultados';
        }

        if (resultsEmpty) {
          resultsEmpty.classList.toggle('hidden', totalResults > 0);
        }

        if (pagination && pageStatus && prevPageButton && nextPageButton) {
          var showPagination = totalResults > currentPerPage;
          pagination.classList.toggle('hidden', !showPagination);
          pageStatus.textContent = 'Pagina ' + currentPage + ' de ' + totalPages;
          prevPageButton.disabled = currentPage <= 1;
          nextPageButton.disabled = currentPage >= totalPages;
        }

        if (shouldSyncUrl !== false) {
          syncUrlWithFilters();
        }
      }

      if (filterSearch) {
        filterSearch.addEventListener('input', function () {
          updateCatalogResults(true, false);
        });
      }

      if (filterSort) {
        filterSort.addEventListener('change', function () {
          updateCatalogResults(true, false);
        });
      }

      sourceButtons.forEach(function (button) {
        button.addEventListener('click', function () {
          setSourceFilter(button.getAttribute('data-filter-source') || 'all');
          if (sourceDropdown) {
            sourceDropdown.removeAttribute('open');
          }
          updateCatalogResults(true, false);
        });
      });

      if (sourceTrigger && sourceDropdown) {
        sourceTrigger.addEventListener('click', function () {
          if (sourceDropdown.hasAttribute('open')) {
            sourceDropdown.removeAttribute('open');
          } else {
            sourceDropdown.setAttribute('open', 'open');
          }
          sourceTrigger.setAttribute('aria-expanded', sourceDropdown.hasAttribute('open') ? 'true' : 'false');
        });

        document.addEventListener('click', function (event) {
          if (!sourceDropdown.contains(event.target)) {
            sourceDropdown.removeAttribute('open');
            sourceTrigger.setAttribute('aria-expanded', 'false');
          }
        });
      }

      perPageButtons.forEach(function (button) {
        button.addEventListener('click', function () {
          setPerPage(button.getAttribute('data-per-page') || '25');
          updateCatalogResults(true, false);
        });
      });

      filterCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
          updateCatalogResults(true, false);
        });
      });

      if (prevPageButton) {
        prevPageButton.addEventListener('click', function () {
          if (currentPage <= 1) {
            return;
          }
          currentPage -= 1;
          updateCatalogResults(true, true);
        });
      }

      if (nextPageButton) {
        nextPageButton.addEventListener('click', function () {
          currentPage += 1;
          updateCatalogResults(true, true);
        });
      }

      if (filterReset) {
        filterReset.addEventListener('click', function () {
          if (filterSearch) {
            filterSearch.value = '';
          }
          if (filterSort) {
            filterSort.value = 'relevance';
          }
          setSourceFilter('all');
          setPerPage('25');
          setCurrentPage('1');
          filterCheckboxes.forEach(function (checkbox) {
            checkbox.checked = false;
          });
          updateCatalogResults(true, false);
        });
      }

      window.addEventListener('popstate', function () {
        readFiltersFromUrl();
        updateCatalogResults(false, true);
      });

      readFiltersFromUrl();
      updateCatalogResults(false, true);
    }

    if (googleLoginButton) {
      googleLoginButton.addEventListener('click', function () {
        setBusy(true);
        clearError();
        request('/auth/google/login').then(function (result) {
          if (!result.ok) {
            showError(result.data && result.data.message ? result.data.message : 'No se pudo iniciar sesion con Google.');
            return;
          }
          applySessionUI(result.data.user || null);
          closeModal();
        }).catch(function () {
          showError('Error de red. Intenta nuevamente.');
        }).finally(function () {
          setBusy(false);
        });
      });
    }

    if (googleRegisterButton) {
      googleRegisterButton.addEventListener('click', function () {
        setBusy(true);
        clearError();
        request('/auth/google/register').then(function (result) {
          if (!result.ok) {
            showError(result.data && result.data.message ? result.data.message : 'No se pudo registrar con Google.');
            return;
          }
          applySessionUI(result.data.user || null);
          closeModal();
        }).catch(function () {
          showError('Error de red. Intenta nuevamente.');
        }).finally(function () {
          setBusy(false);
        });
      });
    }

    var heroCarousel = $('[data-hero-carousel]');
    if (heroCarousel) {
      var slides = $all('[data-carousel-slide]', heroCarousel);
      var dots = $all('[data-carousel-dot]', heroCarousel);
      var prevButton = $('[data-carousel-prev]', heroCarousel);
      var nextButton = $('[data-carousel-next]', heroCarousel);
      var activeIndex = 0;
      var intervalId = null;

      function setSlide(index) {
        var total = slides.length;
        if (!total) {
          return;
        }
        activeIndex = ((index % total) + total) % total;
        slides.forEach(function (slide, idx) {
          slide.classList.toggle('is-active', idx === activeIndex);
        });
        dots.forEach(function (dot, idx) {
          dot.classList.toggle('is-active', idx === activeIndex);
        });
      }

      function nextSlide() {
        setSlide(activeIndex + 1);
      }

      function prevSlide() {
        setSlide(activeIndex - 1);
      }

      function startAutoSlide() {
        stopAutoSlide();
        intervalId = window.setInterval(nextSlide, 6500);
      }

      function stopAutoSlide() {
        if (intervalId) {
          window.clearInterval(intervalId);
          intervalId = null;
        }
      }

      setSlide(0);
      startAutoSlide();

      if (prevButton) {
        prevButton.addEventListener('click', function () {
          prevSlide();
          startAutoSlide();
        });
      }

      if (nextButton) {
        nextButton.addEventListener('click', function () {
          nextSlide();
          startAutoSlide();
        });
      }

      dots.forEach(function (dot, idx) {
        dot.addEventListener('click', function () {
          setSlide(idx);
          startAutoSlide();
        });
      });

      document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
          stopAutoSlide();
        } else {
          startAutoSlide();
        }
      });
    }
  });
})();