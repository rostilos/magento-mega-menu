const defaultOptions = {
    mainWrapperSelector: ".sections.nav-sections",
    menuKey: null,
    menuPosition: null,
    isMainMenu: 1,
    enableSticky: false,
    rootSelector: ".rostilos-mega-menu.level0",
    itemSelector: "li.mega",
    offCanvasBtnSelector: "[data-action=\"toggle-nav\"]",
    offCanvasBreakpoint: 1300,
    offCanvasShowDelay: 50,
    offCanvasHideDelay: 300,
    menuType: 'accordion',
    mobileType: 'accordion', //using in Mobile only when menuType = vertical
    drillOptions: {
        $container: null,
        container: 'drilldown-container',
        root: 'drilldown-root',
        sub: 'drilldown-sub',
        back: 'drilldown-back',
        parentItem: null,
        speed: 200,
        _css: {
            float: 'left',
            width: 320
        },
        _history: []
    },
    extraClass: "",
    mobileDetect: null,
    clientSideRender: false,
    deviceType: null
}
const touchPoints = navigator.maxTouchPoints || (window.DocumentTouch && document instanceof DocumentTouch);

class ubMenu {
    constructor(element, options) {
        this.element = element;
        this.options = {...defaultOptions, ...options};
        this.options.mobileDetect = {
            isMobile : window.innerWidth <= defaultOptions.offCanvasBreakpoint,
            isTablet : window.innerWidth > defaultOptions.offCanvasBreakpoint && touchPoints > 0
        };
        this._updateMenuType();
        this._active();
        this._listen();
    }

    _active() {

        // Reset active state
        const activeItems = this.element.querySelectorAll('.active');
        activeItems.forEach(item => {
            item.classList.remove('active');
        });

        // Set active state for the current selected menu item and all associated parent items
        let activeItem = null;
        const currentItemId = (sessionStorage) ? sessionStorage.getItem('ubMenuItemId') : false;
        const currentUrl = window.location.protocol + '//' + window.location.host + window.location.pathname;
        const urlPath = window.location.pathname;
        const urlPathWithSearch = window.location.pathname + window.location.search;
        if (currentItemId) {
            activeItem = this.element.querySelector('#' + currentItemId);
            if(!activeItem){
                return false;
            }
            if (activeItem.querySelector('a[href="' + currentUrl + '"]') ||
                activeItem.querySelector('a[href="' + urlPath + '"]') ||
                activeItem.querySelector('a[href="' + urlPathWithSearch + '"]')
            ) {
                const aMegaChild = activeItem.querySelector('a.mega');
                const spanMegaChild = activeItem.querySelector('span.mega');
                if (aMegaChild) {
                    activeItem = aMegaChild;
                }
                if (spanMegaChild) {
                    activeItem = spanMegaChild;
                }
            } else {
                activeItem = null;
            }
        } else {
            activeItem = this.element.querySelector("a[href=\"" + currentUrl + "\"]");
            if (!activeItem) {
                activeItem = this.element.querySelector("a[href=\"" + urlPath + "\"]");
            }
            if (!activeItem) {
                activeItem = this.element.querySelector("a[href=\"" + urlPathWithSearch + "\"]");
            }
        }

        if (activeItem?.length) {
            if (activeItem?.length > 1) {
                activeItem = activeItem[0];
            }
            activeItem.classList.add('active');
            let parent = activeItem.parentElement;
            while (parent?.matches(this.options.rootSelector)) {
                parent.classList.add('active');
                parent = parent.parentElement;
            }
            const activeChildItems = this.element.querySelectorAll(`${this.options.itemSelector}.active`);
            activeChildItems.forEach(item => {
                item.children[0].classList.add('active');
            });
            const activeHasChildItems = this.element.querySelectorAll(`${this.options.itemSelector}.has-child.active`);
            activeHasChildItems.forEach(item => {
                item.children[0].classList.add('active');
            });
        }
    }

    _listen() {
        const self = this;
        /**
         * update current clicked menu item id to using on other contexts
         */
        let menuId = null;
        const eventName = (this._isTablet()) ? 'touchstart' : 'click';
        const menuItems = this.element.querySelectorAll(self.options.itemSelector);
        menuItems.forEach(menuItem => {
            menuItem.addEventListener(eventName, function (e) {
                const clickedItem = e.target.closest(self.options.itemSelector);
                if (clickedItem) {
                    menuId = clickedItem.getAttribute('id');
                    sessionStorage.setItem('ubMenuItemId', menuId);
                } else {
                    // reset status of all `A` tags
                    const megaLinks = self.element.querySelectorAll("a.mega");
                    megaLinks.forEach(link => {
                        link.dataset.status = '';
                    });
                }
            });
        });

        let media = 'all';
        if (self.options.offCanvasBreakpoint !== 'all') {
            if (self._isTabletPro()) {
                self.options.offCanvasBreakpoint = '1365';
            }
            media = `(max-width: ${self.options.offCanvasBreakpoint}px)`;
        }

        const mediaQuery = window.matchMedia(media);
        const mediaQueryHandler = function (mediaQuery) {

            if (mediaQuery.matches) {
                if (parseInt(self.options.isMainMenu)) { // is main menu

                    // listen click event on the off-canvas button
                    self._offCanvasMenu();
                } else {
                    self._onCanvasMenu();
                }
            } else {
                // off click event on the off-canvas button
                self._onCanvasMenu();
            }
        };

        mediaQuery.addListener(mediaQueryHandler);
        mediaQueryHandler(mediaQuery);
    }

    _toggleOffCanvasMenu() {
        const htmlElement = document.querySelector('html');

        if (htmlElement.classList.contains('nav-open')) {
            htmlElement.classList.remove('nav-open');
            setTimeout(function () {
                htmlElement.classList.remove('nav-before-open');
            }, this.options.offCanvasHideDelay);
        } else {
            htmlElement.classList.add('nav-before-open');
            setTimeout(function () {
                htmlElement.classList.add('nav-open');
            }, this.options.offCanvasShowDelay);
        }
    }

    _offCanvasMenu() {
        // Add extra class 'nav-off-canvas'
        const pageWrapper = this.element.closest('.page-wrapper');
        if(pageWrapper){
            pageWrapper.classList.add('nav-off-canvas');
        }

        // Remove event bindings on the menu items from _onCanvasMenu()
        const menuItems = this.element.querySelectorAll(this.options.itemSelector + ", a.mega, span.mega");
        menuItems.forEach(item => {
            item.removeEventListener('click', this._handleItemClick);
            item.removeEventListener('touchstart', this._handleItemClick);
            item.removeEventListener('mouseenter', this._handleItemMouseEnter);
            item.removeEventListener('mouseleave', this._handleItemMouseLeave);
        });

        // Apply vertical menu
        this._verticalMenu();
    }

    _onCanvasMenu() {
        if (this.options.menuType === 'vertical' ||
            this.options.menuType === 'drilldown' ||
            this.options.menuType === 'accordion') {
            this._verticalMenu();
        } else if (this.options.menuType === 'footer_menu') {
            this._footerMenu();
        } else {
            // Remove extra class 'nav-off-canvas'
            const pageWrapper = this.element.closest('.page-wrapper');
            if(pageWrapper){
                pageWrapper.classList.remove('nav-off-canvas');
            }
            // Apply Horizontal menu
            this._horizontalMenu();
        }
    }

    _updateMenuType() {
        // Check if the menu type needs to be updated based on mobile and tablet conditions
        if ((this._isMobile() || this._isTabletPortrait()) &&
            (this.options.menuType === 'vertical' || this.options.menuType === 'horizontal')) {
            this.options.menuType = this.options.mobileType;
        }


        // Add extra class by menu type
        this.element.classList.add(this.options.menuType + "-root");

        // Add extra class if sticky enabled
        if (parseInt(this.options.enableSticky) && parseInt(this.options.isMainMenu)) {
            const pageWrapper = this.element.closest('div.page-wrapper');
            if (pageWrapper) {
                pageWrapper.classList.add('rs-nav-sticky');

                // Handle states of sticky element using IntersectionObserver
                const observer = new IntersectionObserver(entries => {
                    if (entries[0].intersectionRatio === 0) {
                        pageWrapper.classList.add('sticky-fired');
                        if (!pageWrapper.classList.contains('nav-off-canvas')) {
                            const navSections = document.querySelector('div.page-wrapper > .sections.nav-sections');
                            const headerContent = document.querySelector('div.page-wrapper > .page-header > .header.content');
                            if (navSections && !headerContent.contains(navSections)) {
                                headerContent.insertBefore(navSections, headerContent.querySelector('.logo'));
                                navSections.dataset.posChanged = 1;
                            }
                        }
                    } else if (entries[0].intersectionRatio === 1) {
                        pageWrapper.classList.remove('sticky-fired');
                        if (!pageWrapper.classList.contains('nav-off-canvas')) {
                            const navSections = document.querySelector('div.page-wrapper > .header.content > .sections.nav-sections');
                            if (navSections?.dataset.posChanged) {
                                document.querySelector('div.page-wrapper > .page-header').appendChild(navSections);
                            }
                        }
                    }
                }, {threshold: [0, 1]});

                observer.observe(document.querySelector("#rs-top-bar"));
            }
        }
    }

    _verticalMenu() {
        // Apply selected menu type
        if (this.options.menuType === 'accordion') {
            this._accordionMenu();
        } else if (this.options.menuType === 'drilldown') {
            this._drillDownMenu();
        } else {
            // Apply vertical style as default
            this._commonEvents();
        }
    }

    _horizontalMenu() {
        if (this._isTablet()) {
            if (this.options.menuType === 'drilldown') {
                document.querySelector(this.options.drillOptions.$container).style.minHeight = '';
            }
        }

        this._commonEvents();
    }

    _commonEvents() {
        const self = this;

        // reset events
        const menuItemsWithChild = this.element.querySelectorAll("li.has-child");
        for (const menuItem of menuItemsWithChild) {
            menuItem.removeEventListener('touchstart', this._handleMenuItemClick);
            menuItem.removeEventListener('click', this._handleMenuItemClick);
            const menuItemChildren = menuItem.children;
            for (const child of menuItemChildren) {
                child.removeEventListener('touchstart', this._handleMenuItemClick);
                child.removeEventListener('click', this._handleMenuItemClick);
            }
        }

        // binding common events on tablet and desktop
        const menuItems = this.element.querySelectorAll("a.mega, span.mega");
        const eventName = (this._isTablet()) ? 'click' : 'mouseenter';
        for (const menuItem of menuItems) {
            menuItem.addEventListener(eventName, function (e) {
                if (e.target.tagName === 'A') {
                    e.preventDefault();
                }
                if (this.classList.contains('style-tabs') || this.classList.contains('style-tabs-hz')) {
                    self._tabs(this);
                }
                // if a menu item is a tab head (used extra class 'tab-head')
                if (this.classList.contains('tab-head')) {
                    const tabHead = this.parentElement.closest('li.tab-head');
                    self._activeTab(tabHead);
                    if(!self._isTablet()){
                        tabHead.addEventListener('mouseleave', () => {
                            self._deactiveTab(tabHead);
                        })

                    }
                }

                // get current status of menu item
                const status = this.getAttribute('data-status');
                // reset status of all menu items
                for (const menuItem of menuItems) {
                    menuItem.removeAttribute('data-status');
                }

                if (self._isTablet()) {
                    if (!this.classList.contains('has-child') ||
                        (this.classList.contains('has-child') && status !== undefined && status === 'touched')
                    ) {
                        const link = this.getAttribute('href');
                        if (link !== undefined && link.length) {
                            window.location.href = link;
                        }
                        return true;
                    } else {
                        this.setAttribute('data-status', 'touched');
                        e.preventDefault();
                    }
                } else {
                    return true;
                }

                return false;
            });
        }

        if (this._isTablet()) {
            document.addEventListener('click', function (e) {
                const menuArea = self.element.closest(self.options.rootSelector);
                if (!menuArea?.contains(e.target)) {
                    const activeItems = self.element.querySelectorAll(self.options.itemSelector + '.active');
                    for (const activeItem of activeItems) {
                        activeItem.classList.remove('active');
                        const childElements = activeItem.children;
                        for (const child of childElements) {
                            child.classList.remove('active');
                        }
                    }
                }
            });
        }

        if (this._isDesktop() || this._isTablet()) {
            const items = this.element.querySelectorAll(self.options.itemSelector);
            for (const item of items) {
                if (!item.classList.contains('tab-head')) {
                    item.addEventListener('mouseenter', function () {
                        this.classList.add('mega-hover');
                    });
                    item.addEventListener('mouseleave', function () {
                        this.classList.remove('mega-hover');
                    });
                }
            }
        }
    }

    _footerMenu() {
        //footer menu
    }

    _tabs(item) {
        const self = this;

        // check and get the needed tab to active
        let activeTab = null;
        const tabsWrapper = item.nextElementSibling?.querySelector('ul.level2');
        const tabHeads = tabsWrapper?.querySelectorAll('li.tab-head');
        if(!tabHeads){
            return;
        }
        for (const tabHead of tabHeads) {
            if (tabHead.querySelector('a.mega.active') || tabHead.querySelector('span.mega.active')) {
                activeTab = tabHead;
                break;
            }
        }

        if (!activeTab && tabHeads.length > 0) {
            activeTab = tabHeads[0];
        }

        const lastOpenedId = sessionStorage.getItem('ubLastOpenedTabId');
        if (lastOpenedId) {
            const openedTab = tabsWrapper.querySelector('#' + lastOpenedId);
            if (openedTab) {
                activeTab = openedTab;
            }
        }

        if (activeTab) {
            if (!activeTab.classList.contains('active')) {
                self._activeTab(activeTab);
            } else {
                self._resizeTab(activeTab);
            }
        }
    }

    _activeTab(tabHead) {
        tabHead.classList.add('active');
        const siblingTabHeads = Array.from(tabHead.parentNode.querySelectorAll('li.tab-head'));
        siblingTabHeads.forEach(siblingTabHead => {
            if (siblingTabHead !== tabHead) {
                siblingTabHead.classList.remove('active');
            }
        });

        this._resizeTab(tabHead);
    }

    _deactiveTab(tabHead) {
        tabHead.classList.remove('active');
    }

    _resizeTab(tabHead) {
        // Auto set min-height for wrapper of current tabs
        const tabContent = tabHead.querySelector('.child-content');
        if(!tabContent){
            return;
        }
        tabContent.style.cssText= 'min-height: 0px !important';
        const minHeight = parseInt(getComputedStyle(tabContent).height) + (parseInt(getComputedStyle(tabContent).top));
        tabContent.style = '';
        const parentChildContent = tabHead.closest('div.child-content');
        if (parentChildContent) {
            parentChildContent.style.minHeight = minHeight + 'px';
            if(parentChildContent.querySelector('div.child-content-inner')){
                parentChildContent.querySelector('div.child-content-inner').style.minHeight = minHeight + 'px';
            }
        }
    }

    _accordionMenu() {
        const self = this;

        // Add extra class 'scroll-enabled'
        if (parseInt(self.options.isMainMenu) && (self._isTabletLandscape() || self._isDesktop())) {
            const mainWrapper = this.element.closest(self.options.mainWrapperSelector);
            mainWrapper.classList.add('scroll-enabled');
        }

        // Binding events on items that have sub items
        const eventName = (this._isTablet()) ? 'touchstart' : 'click';
        const menuItems = this.element.querySelectorAll("a.has-child, span.has-child");
        menuItems.forEach(function(item) {
            item.addEventListener(eventName, function(e) {
                let preventDefault = false;
                if (e.target.tagName === 'A' || e.target.closest('a.mega')) {
                    preventDefault = true;
                }

                // --- START FIX ---

                // 1. Check if the item we're clicking was already active
                const wasActive = this.classList.contains('active');

                // 2. Get the parent <li> of the clicked <a>/<span>
                const currentLi = this.parentElement;

                // 3. Get all sibling <li> elements (including the current one)
                const allListItems = Array.from(currentLi.parentElement.children);

                // 4. Deactivate ALL accordions at this level
                allListItems.forEach(function(li) {
                    // Remove active from the <a> or <span> trigger

                    li.querySelector("a.has-child, span.has-child")?.classList.remove('active');
                    li.classList.remove('active');

                    // Remove active from the child-content div
                    li.querySelector("div.child-content")?.classList.remove('active');
                });

                // 5. If the clicked item was NOT active, open it.
                if (!wasActive) {
                    // Add 'active' back to the clicked trigger
                    this.classList.add('active');

                    // Add 'active' to its sibling child-content div
                    const childContent = currentLi.querySelector("div.child-content");
                    if (childContent) {
                        currentLi.classList.add('active');
                        childContent.classList.add('active');
                    }
                }

                // --- END FIX ---

                if (preventDefault) {
                    e.preventDefault();
                }
            });
        });

        // Bind click event on menu item group links (shop all items)
        const shopAllItems = this.element.querySelectorAll("span.menu-group-link");
        shopAllItems.forEach(function(item) {
            item.addEventListener('click', function(e) {
                const url = this.previousElementSibling.getAttribute('href');
                if (url !== undefined && url.length && url !== '#') {
                    window.location.href = url;
                    sessionStorage.setItem('ubMenuItemId', this.parentElement.getAttribute('id'));
                }
            });
        });
    }

    _drillDownMenu() {
        const self = this;

        // Wrapper more tags using for drilldown function
        if (!self.element.closest('.drilldown-container')) {
            const drilldownDiv = document.createElement('div');
            drilldownDiv.classList.add('drilldown');
            const drilldownContainerDiv = document.createElement('div');
            drilldownContainerDiv.classList.add('drilldown-container');
            if(self.element.parentNode){
                self.element.parentNode.insertBefore(drilldownDiv, self.element);
            }
            drilldownDiv.appendChild(drilldownContainerDiv);
            drilldownContainerDiv.appendChild(self.element);

            let backText = 'Back';
            // Append drilldown buttons
            const ddButtons = '<div class="btn-drilldown" style="display: none;">' +
                '<button class="btn-back" type="button">' + backText + '</button>' +
                '</div>';
            const parentDrilldownDiv = self.element.closest('.drilldown');
            parentDrilldownDiv.insertAdjacentHTML('afterbegin', ddButtons);

            // Set drilldown container element
            self.options.drillOptions.$container = self.element.parentElement;
            parentDrilldownDiv.addEventListener('click', function (event) {
                if (event.target.classList.contains('btn-back')) {
                    self._up({});
                }
            });
        }

        // Binding events on items has sub items
        const eventName = (this._isTablet()) ? 'touchstart' : 'click';
        const menuItems = self.element.querySelectorAll("a.has-child, span.has-child");
        menuItems.forEach(function (menuItem) {
            menuItem.addEventListener(eventName, function (e) {
                let preventDefault = false;
                if (e.target.tagName === 'A' || e.target.closest("a.mega")) {
                    preventDefault = true;
                }

                const nextSibling = this.nextElementSibling;
                if (nextSibling?.classList.contains(self.options.drillOptions.sub)) {
                    self._down(nextSibling, {});
                } else {
                    preventDefault = false;
                }

                if (preventDefault) {
                    e.preventDefault();
                }
            });
        });

        // Fixed styles
        self._autoStyles(this.element);
    }

    _down(next, opts) {
        const self = this;
        if (!next) {
            return;
        }


        // Re-calculate width for drilldown container
        // self.options.drillOptions._css.width = self.element.offsetWidth;
        self.options.drillOptions.$container.style.width = (self.options.drillOptions._css.width * 2) + 'px';

        // Mark parent of the opened node
        next.parentElement.setAttribute("data-is-parent", true);

        // Get the parent item
        let parentItem = next.previousElementSibling;
        if (!parentItem) {
            parentItem = next.previousElementSibling;
        }
        self.options.drillOptions.parentItem = parentItem;

        let parentElement = null;
        if (parentItem.getAttribute("href") !== null) {
            parentElement = '<a class="parent-item" href="' + parentItem.getAttribute("href") +
                '"><span>' + 'All ' + parentItem.textContent + '</span></a>';
        } else {
            parentElement = '<span class="parent-item">' + parentItem.textContent + '</span>';
        }
        if (!next.querySelector('.parent-item')) {
            next.insertAdjacentHTML('afterbegin', parentElement);
        }

        // Update needed CSS classes
        next.classList.remove('child-content');
        next.classList.remove(self.options.drillOptions.sub);
        next.classList.add(self.options.drillOptions.root);
        if(self.options?.extraClass){
            next.classList.add(self.options.extraClass);

        }

        // Append to drilldown container
        next.style.left = (2 * self.options.drillOptions._css.width) + 'px';
        self.options.drillOptions.$container.appendChild(next);

        const speed = (opts && opts.speed !== undefined) ? opts.speed : self.options.drillOptions.speed;
        self._drilling({marginLeft: (-1 * self.options.drillOptions._css.width) + "px", speed: speed},
            function() {
                next.style.left = '0px';
                const $current = next.previousElementSibling;

                self.options.drillOptions._history.push(self._detach($current));
                self._restoreState(next);

                self.options.drillOptions.$container.parentElement.parentElement.classList.add('drilling');
                self.options.drillOptions.$container.parentElement.querySelector('.btn-drilldown').style.display = 'block';

                // Fixed styles
                self._autoStyles();
            }
        );
    }

    _detach(elem) {
        return elem.parentElement.removeChild(elem);
    }

    _up(opts) {
        const self = this;

        // Re-calculate width for drilldown container
        // self.options.drillOptions._css.width = self.element.offsetWidth;
        self.options.drillOptions.$container.style.width = (self.options.drillOptions._css.width * 2) + 'px';
        // Get node element to backward and prepend it to container
        const back = self.options.drillOptions._history.pop();

        if (back === undefined) {
            return;
        }

        back.style.left = (-1 * self.options.drillOptions._css.width) + 'px';
        self.options.drillOptions.$container.insertBefore(back, self.options.drillOptions.$container.firstChild);

        const speed = (opts && opts.speed !== undefined) ? opts.speed : self.options.drillOptions.speed;
        self._drilling({marginLeft: '0px', speed: speed},
            function () {
                self._animate(back, 'left', '0px', speed);
                const current = back.nextElementSibling;
                current.classList.add(self.options.drillOptions.sub);
                current.classList.remove(self.options.drillOptions.root);
                if(self.options?.extraClass){
                    current.classList.remove(self.options.extraClass);
                }

                // Restore to the node element at its initial position in the Menu DOM tree
                const parentWithDataAttr = self.options.drillOptions.$container.querySelector('[data-is-parent]');
                parentWithDataAttr.removeAttribute('data-is-parent');
                parentWithDataAttr.appendChild(current);

                if(current.querySelector('.menu-group-link')){
                    current.querySelector('.menu-group-link').style.display = 'none';
                }
                if(current.querySelector('.menu-group-link')) {
                    current.querySelector('.drilldown-back').style.display = 'none';
                }

                self._restoreState(back);

                if (back.classList.contains('level0')) {
                    self.options.drillOptions.$container.parentNode.parentNode.classList.remove('drilling');
                    self.options.drillOptions.$container.parentNode.querySelector('.btn-drilldown').style.display = 'none';
                }

                // Fixed styles
                self._autoStyles();
            }
        )
    }

    _drilling(opts, callback) {
        const drillOptions = this.options.drillOptions;
        const container = drillOptions.$container;
        const rootClass = drillOptions.root;
        const speed = opts.speed || drillOptions.speed;
        const marginLeft = opts.marginLeft;

        const menus = container.querySelectorAll('.' + rootClass);
        menus.forEach(menu => {
            Object.assign(menu.style, drillOptions._css);
        });

        const menu = menus[0];
        this._animate(menu, 'left', marginLeft, speed, callback);
    }

    _animate(element, property, targetValue, duration, callback) {
        const initialValue = parseFloat(getComputedStyle(element)[property]);
        const startTime = performance.now();

        function update(time) {
            const elapsedTime = time - startTime;
            if (elapsedTime >= duration) {
                element.style[property] = targetValue;
                if (callback) callback();
                return;
            }
            const progress = elapsedTime / duration;
            const currentValue = initialValue + (progress * (parseFloat(targetValue) - initialValue));
            element.style[property] = currentValue + 'px';
            requestAnimationFrame(update);
        }

        requestAnimationFrame(update);
    }
    _restoreState(node) {
        node.style.cssText = `
        float: '';
        width: '';
        left: '';
        right: '';
    `;
        // reset width of drilldown container
        this.options.drillOptions.$container.style.width = '';
    }

    _isMobile() {
        if(this.options.clientSideRender){
            return this.options.deviceType === 'mobile';
        }else{
            return this.options.mobileDetect.isMobile;
        }
    }

    _isTablet() {
        if(this.options.clientSideRender){
            return this.options.deviceType === 'tablet';
        }else{
            return this.options.mobileDetect.isTablet;
        }
    }

    _isTabletPro() {
        let result = false;
        if (this._isTablet()) {
            const ratio = window.devicePixelRatio || 1;
            const screen = {
                width: window.screen.width * ratio,
                height: window.screen.height * ratio
            };
            result = (screen.width === 2048 && screen.height === 2732) ||
                (screen.width === 2732 && screen.height === 2048);
        }
        return result;
    }

    _isTabletPortrait() {
        const result = (this._isTablet() && matchMedia('all and (orientation:portrait)').matches) ?
            true :
            false;

        return result;
    }

    _isTabletLandscape() {
        const result = (this._isTablet() && matchMedia('all and (orientation:landscape)').matches) ?
            true :
            false;

        return result;
    }

    _isDesktop() {
        if(this.options.clientSideRender){
            return this.options.deviceType === 'desktop';
        }else{
            return (!this._isMobile() && !this._isTablet()) ? true : false;
        }
    }

    _autoStyles() {
        const drillOptions = this.options.drillOptions;
        const drillDownRoot = drillOptions.$container.querySelector('.' + drillOptions.root);

        let h = drillDownRoot.offsetHeight; // Height without margins
        const backBtn = drillOptions.$container.previousElementSibling.querySelector('.btn-drilldown');
        if (backBtn && window.getComputedStyle(backBtn).display === 'block') {
            h += backBtn.offsetHeight;
        }

        drillOptions.$container.style.minHeight = h + 'px';
    }

    _reinit(){
        const elem = document.querySelector(this.options.offCanvasBtnSelector);
        elem.replaceWith(elem.cloneNode(true));
    }
}
