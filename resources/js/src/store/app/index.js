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
    groups: [],
    topics: [],
    contents: [],
    comments:[],
    selectedOrder: {},
    selectedWebsite: {},
    selectedTopic: {},
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
    },
    setGroups(state, val) {
      state.groups = val;
    },
    setTopics(state, val) {
      state.topics = val;
    },
    setContents(state, val) {
      state.contents = val;
    },
    setComments(state, val) {
      state.comments = val;
    },
    setSelectedOrder(state, val) {
      state.selectedOrder = val;
    },
    setSelectedWebsite(state, val) {
      state.selectedWebsite = val;
    },
    setSelectedTopic(state, val) {
      state.selectedTopic = val;
    },
  },
  actions: {
    loadAppData({commit, state, dispatch}, payload){
      dispatch('loadTop');
      dispatch('loadData');
      dispatch('loadMenu');
      
    },
    loadTop({commit, state, dispatch}) {
      axios.post(state.apiBaseUrl+'dashboard/data', {parts: 'top_bar'}).then(response => {
        console.log('top data');
        // console.log(response.data);
        let top = {
          websites: response.data.data.websites,
          languages: response.data.data.languages,
          notifications: response.data.data.notifications
        };
        var userData = JSON.parse(localStorage.getItem('userData'));
        commit('setTopBar', top);
        commit('setSelectedWebsite', response.data.data.websites[0]);
        dispatch('loadTopics', {website:response.data.data.websites[0].id})
        userData.top_bar = top;
        localStorage.setItem('userData', JSON.stringify(userData))
      }).catch(error => {
        console.log('error load top data');
      })
    },
    loadData({commit, state, dispatch}) {
      axios.post(state.apiBaseUrl+'dashboard/data', {parts: 'data'}).then(response => {
        console.log('dash data');
        // console.log(response.data);
        let data = {
          statistics: response.data.data.statistics,
          topic_lists: response.data.data.topic_lists,
          article_lists: response.data.data.article_lists
        };
        var userData = JSON.parse(localStorage.getItem('userData'));
        commit('setDashboardData', data);
        userData.stat = data;
        localStorage.setItem('userData', JSON.stringify(userData))
      }).catch(error => {
        console.log('error load dash data');
      })
    },
    loadMenu({commit, state, dispatch}) {
      axios.post(state.apiBaseUrl+'dashboard/data', {parts: 'side_menu'}).then(response => {
        console.log('menu data');
        // console.log(response.data);
        let menu = {
          side_menus: response.data.data.side_menus
        };
        var userData = JSON.parse(localStorage.getItem('userData'));
        commit('setMenu', menu);
        userData.menu = menu;
        localStorage.setItem('userData', JSON.stringify(userData))
      }).catch(error => {
        console.log('error load menu data');
      })
    },
    loadTopics({commit, state, dispatch}, payload) {
      if(payload.website) {        
        axios.post(state.apiBaseUrl+'primary-topic/list-by-website', payload).then((res) => {
          console.log('topics list');
          commit('setTopics', res.data.data.topics);
          commit('setGroups', res.data.data.groups);
        }).catch(() => {
          console.log('error load topics data');
        })
      }
    },
    sortRecord({commit, state, dispatch}, payload) {
      axios.post(state.apiBaseUrl+'topic/sort-record', payload).then((res) => {
        console.log('sort topics');
        commit('setTopics', res.data.data);
      }).catch(() => {
        console.log('error load sort record data');
      })
    },
    topicStatusUpdate({commit, state, dispatch}, payload) {
      axios.post(state.apiBaseUrl + 'topic/update-status', payload).then((res) => {
        commit('setSelectedTopic', res.data.data)
      }).catch((error) => {
        console.log('error update topic status');
      })
    }

  },
}
