import { $themeBreakpoints } from '@themeConfig'
import axios from '@axios';

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
  actions: {
    loadAppData({commit, state, dispatch}, payload){
      axios.post(store.state.app.apiBaseUrl+'dashboard/data').then(response => {
        console.log('dashboard data');
        console.log(response);
        console.log(response.data);
        let top = {
          websites: response.data.data.websites,
          languages: response.data.data.languages,
          notifications: response.data.data.notifications
        };
        let menu = {
          side_menus: response.data.data.side_menus
        };
        let data = {
          statistics: response.data.data.statistics,
          topic_lists: response.data.data.topic_lists,
          article_lists: response.data.data.article_lists
        };
        commit('app/setTopBar', top);
        userData.top_bar = top;
        commit('app/setMenu', menu);
        userData.menu = menu;
        commit('app/setDashboardData', data);
        userData.stat = data;
        localStorage.setItem('userData', JSON.stringify(userData))
      }).catch(error => {

      })
    }
  },
}
