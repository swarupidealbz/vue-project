import { $themeBreakpoints } from '@themeConfig'

export default {
  namespaced: true,
  state: {
    windowWidth: 0,
    shallShowOverlay: false,
    apiBaseUrl: 'https://cl.99ideaz.com/api/',
    topBar: {
      websites: [],
      languages: [],
      notifications: []
    },
    dashboardData: {
      statistics: [],
      topic_lists: [],
      article_lists: []
    },
    menu: {
      side_menus: []
    },
  },
  getters: {
    currentBreakPoint: state => {
      const { windowWidth } = state
      if (windowWidth >= $themeBreakpoints.xl) return 'xl'
      if (windowWidth >= $themeBreakpoints.lg) return 'lg'
      if (windowWidth >= $themeBreakpoints.md) return 'md'
      if (windowWidth >= $themeBreakpoints.sm) return 'sm'
      return 'xs'
    },
  },
  mutations: {
    UPDATE_WINDOW_WIDTH(state, val) {
      state.windowWidth = val
    },
    TOGGLE_OVERLAY(state, val) {
      state.shallShowOverlay = val !== undefined ? val : !state.shallShowOverlay
    },
    setTopBar(state, val) {
      state.topBar = val;
    },
    setMenu(state, val) {
      state.menu = val;
    },
    setDashboardData(state, val) {
      state.dashboardData = val;
    }
  },
  actions: {},
}
