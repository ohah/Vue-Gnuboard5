import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router'
import Home from '@/views/Home.vue'
import List from '@/views/bbs/List.vue'
import Board from '@/views/bbs/Board.vue'
import Search from '@/views/bbs/Search.vue'
import Write from '@/views/bbs/Write.vue'
import Point from '@/views/bbs/Point.vue'
import Memo from '@/views/bbs/Memo.vue'
import Scrap from '@/views/bbs/Scrap.vue'
import Profile from '@/views/bbs/Profile.vue'
import Register from '@/views/bbs/Register.vue'
import Content from '@/views/bbs/Content.vue'
import New from '@/views/bbs/New.vue'

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path : '/bbs/board',
    name : '/bbs/board',
    component: Board,
  },
  {
    path : '/bbs/new',
    name : '/bbs/new',
    component: New,
  },
  {
    path: '/bbs/search',
    name: 'Search',
    component: Search
  },
  {
    path: '/bbs/write',
    name: '/bbs/write',
    component: Write
  },
  {
    path: '/bbs/content',
    name: '/bbs/content',
    component: Content
  },
  {
    path: '/bbs/point',
    name: '/bbs/point',
    component: Point
  },
  {
    path: '/bbs/memo',
    name: '/bbs/memo',
    component: Memo
  },
  {
    path: '/bbs/memo_form',
    name: '/bbs/memo_form',
    component: Memo
  },
  {
    path: '/bbs/scrap',
    name: '/bbs/scrap',
    component: Scrap
  },
  {
    path: '/bbs/profile',
    name: '/bbs/profile',
    component: Profile
  },
  {
    path: '/write',
    name: 'Write',
    component: Write
  },
  {
    path : '/board',
    name : 'board',
    component: Board,
  },
  {
    path : '/:bo_table/:wr_id',
    name : 'board',
    component: Board,
  },
  {
    path : '/:bo_table',
    name : 'board',
    component: Board,
  },
  
  {
    path: '/List',
    name: 'List',
    component: List
  },
  {
    path: '/bbs/regsiter',
    name: 'Register',
    component: Register,
    children:[
      {
        path  : "/",
        name : 'RegsiterAgree',
        component: () => import("@/components/bbs/RegsiterAgree.vue")
      },
      {
        path  : "join",
        name : 'RegsiterJoin',
        component: () => import("@/components/bbs/RegsiterJoin.vue")
      },
      {
        path  : "complete",
        name : 'RegsiterComplete',
        component: () => import("@/components/bbs/RegsiterComplete.vue")
      }

    ]
  },
  /*
  {
    path: '/about',
    name: 'About',
    // route level code-splitting
    // this generates a separate chunk (about.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () => import('../views/About.vue')
  }
  */
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

export default router
