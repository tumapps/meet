// Register global components
export default {
  install: (app) => {
    app.component('tab-nav', require('@/components/bootstrap/tab/TabNav').default)
    app.component('tab-nav-items', require('@/components/bootstrap/tab/TabNavItems').default)
    app.component('tab-content', require('@/components/bootstrap/tab/TabContent').default)
    app.component('tab-content-item', require('@/components/bootstrap/tab/TabContentItem').default)
    // Tab Components
    app.component('tab-button', require('@/components/bootstrap/tab/TabButton').default)
    app.component('tab-pane', require('@/components/bootstrap/tab/TabPane').default)

    // Brand Components
    app.component('brand-logo', require('@/components/custom/logo/BrandLogo').default)
    app.component('brand-name', require('@/components/custom/logo/BrandName').default)

    // Icon Components
    app.component('icon-component', require('@/components/icons/IconComponent').default)

    // Form Components
    app.component('select-component', require('@/components/custom/select/SelectComponent').default)
    app.component('qty-button', require('@/components/custom/elements/QtyButton.vue').default)
  }
}
