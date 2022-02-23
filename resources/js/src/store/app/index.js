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
    contentData: [],
    selectedNotificationType: '',
    allNotifications: [],
    userData: {},
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
    setContentData(state, val) {
      state.contentData = val;
    },
    setSelectedNotificationType(state, val) {
      state.selectedNotificationType = val
    },
    setAllNotifications(state, val) {
      state.allNotifications = val
    },
    setUserData(state, val) {
      state.userData = val
    }
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
        let web = localStorage.getItem('website')
        commit('setSelectedWebsite', response.data.data.websites[0]);
        localStorage.setItem('website', JSON.stringify(response.data.data.websites[0]))
        if(web) {
          web  = JSON.parse(web);
          let sweb = response.data.data.websites.some(w => w.id === web.id)
          if(sweb) {
            commit('setSelectedWebsite', web);
            localStorage.setItem('website', JSON.stringify(web))
          }
        }
        dispatch('loadTopics', {website:state.selectedWebsite.id})
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
      return new Promise((resolve, reject) => {
          axios.post(state.apiBaseUrl + 'topic/update-status', payload).then((res) => {
            if(res.data.data.length == 1 && (res.data.status == true)) {
              commit('setSelectedTopic', res.data.data[0])
            }
            resolve(res.data)
          }).catch((error) => {
            reject(error.response);
          console.log('error update topic status');
        })
      })
    },
    addOrUpdateTopic({commit, state, dispatch}, payload) {
      return new Promise((resolve, reject) => {
          axios.post(state.apiBaseUrl + 'topic/create', payload).then((res) => {
            resolve(res.data)
          }).catch((error) => {
            reject(error.response);
          console.log('error add topic');
        })
      })
    },
    setFavorite({commit, state, dispatch}, payload) {
      return new Promise((resolve, reject) => {
          axios.post(state.apiBaseUrl + 'topic/favorite', payload).then((res) => {
            commit('setSelectedTopic', res.data.data)
            resolve(res.data)
          }).catch((error) => {
            reject(error.response);
          console.log('error set favorite topic status');
        })
      })
    },
    setUnfavorite({commit, state, dispatch}, payload) {
      return new Promise((resolve, reject) => {
          axios.post(state.apiBaseUrl + 'topic/unfavorite', payload).then((res) => {
            commit('setSelectedTopic', res.data.data)
            resolve(res.data)
          }).catch((error) => {
            reject(error.response);
          console.log('error set unfavorite topic status');
        })
      })
    },
    loadContent({commit, state, dispatch}, payload) {
      return new Promise((resolve, reject) => {
        axios.post(state.apiBaseUrl + 'content/list-for-timeline', payload).then(res => {
          commit('setContentData', res.data.data.content_comment)
          resolve(res.data)
        }).catch(error => {
          reject(error.response)
        })
      })
    },
    addEditComment({commit, state, dispatch}, payload) {
      return new Promise((resolve, reject) => {
        axios.post(state.apiBaseUrl + 'comment/add-edit', payload).then(res => {
          commit('setContentData', res.data.data.content_comment)
          resolve(res.data)
        }).catch(error => {
          reject(error.response)
        })
      })
    },
    addOrUpdateContent({commit, state, dispatch}, payload) {
      return new Promise((resolve, reject) => {
          axios.post(state.apiBaseUrl + 'content/create', payload).then((res) => {
            resolve(res.data)
          }).catch((error) => {
            reject(error.response);
          console.log('error add content');
        })
      })
    },
    loadNotifications({commit, state, dispatch}, payload) {
      return new Promise((resolve, reject) => {
          axios.post(state.apiBaseUrl + 'notifications', payload).then((res) => {
            commit('setAllNotifications', res.data.data)
            resolve(res.data)
          }).catch((error) => {
            reject(error.response);
          console.log('error load notification');
        })
      })
    },
    updateNotification({commit, state, dispatch}, payload) {
      return new Promise((resolve, reject) => {
          axios.post(state.apiBaseUrl + 'notification/update', payload).then((res) => {
            commit('setAllNotifications', res.data.data)
            resolve(res.data)
          }).catch((error) => {
            reject(error.response);
          console.log('error update notification');
        })
      })
    },
    changePassword({state, commit, dispatch}, payload) {
      return new Promise((resolve, reject) => {
        axios.post(state.apiBaseUrl + 'password/change-password', payload).then(res => {
          resolve(res.data)
        }).catch(err => {
          reject(err.response)
          console.log('error password update')
        })
      })
    },
    updateProfile({state, commit, dispatch}, payload) {
      return new Promise((resolve, reject) => {
        axios.post(state.apiBaseUrl + 'profile/update', payload).then(res => {
          resolve(res.data)
          state.userData = res.data.data
        }).catch(err => {
          reject(err.response)
          console.log('error update profile')
        })
      })
    },
    updateProfileImage({state, commit, dispatch}, payload) {
      return new Promise((resolve, reject) => {
        axios.post(state.apiBaseUrl + 'profile/update-image', payload, {
          headers: {
           'content-type': 'multipart/form-data' // do not forget this 
          }
        }).then(res => {
          resolve(res.data)
          state.userData = res.data.data
        }).catch(err => {
          reject(err.response)
          console.log('error update profile image')
        })
      })
    }
  },
}
