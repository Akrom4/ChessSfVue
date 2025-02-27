<template>
  <div ref="reactRoot"></div>
</template>

<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref } from 'vue'
import React from 'react'
import ReactDOM from 'react-dom/client'
import App from '@/chess/App.jsx'  // now with .jsx extension

// Define your pgnData (or import it from somewhere)
const pgnData = JSON.stringify({
  title: "",
  chapter: [
    {
      FEN: "",
      Moves: [
        { move: "e4", position: "rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1", teamColor: "w", moveNumber: 1 },
        { move: "e5", position: "rnbqkbnr/pppp1ppp/8/4p3/4P3/8/PPPP1PPP/RNBQKBNR w KQkq e6 0 1", teamColor: "b", moveNumber: 1 }
      ],
      Title: "",
      Number: 1,
      Comments: [],
      Variations: []
    }
  ]
})

const reactRoot = ref<HTMLElement | null>(null)
let root: ReturnType<typeof ReactDOM.createRoot> | null = null

onMounted(() => {
  if (reactRoot.value) {
    root = ReactDOM.createRoot(reactRoot.value)
    root.render(
      React.createElement(
        React.StrictMode,
        null,
        React.createElement(App, { pgnDataProp: pgnData })
      )
    )
  }
})

onBeforeUnmount(() => {
  if (root) {
    root.unmount()
  }
})
</script>
