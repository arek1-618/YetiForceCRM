<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-layout view="hHh LpR fFf" container :class="['bg-white', miniMode ? 'chat-mini' : '']">
    <chat-header @visibleInputSearch="inputSearchVisible = $event" @showTabHistory="tabHistoryShow = $event" />
    <left-panel />
    <right-panel />
    <main-panel />
    <chat-footer />
  </q-layout>
</template>
<script>
import LeftPanel from './LeftPanel.vue'
import RightPanel from './RightPanel.vue'
import MainPanel from './MainPanel.vue'
import Messages from './Messages.vue'
import ChatHeader from './ChatHeader.vue'
import ChatFooter from './ChatFooter.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters } = createNamespacedHelpers('Chat')
export default {
  name: 'Chat',
  components: { LeftPanel, RightPanel, MainPanel, ChatHeader, ChatFooter },
  props: {
    parentRefs: { type: Object, required: true }
  },
  data() {
    return {}
  },
  computed: {
    ...mapGetters(['leftPanel', 'rightPanel', 'miniMode'])
  }
}
</script>
<style>
</style>
