<template>
  <div :class="className">
    <div v-if="piece" :style="{ backgroundImage: `url(${piece})` }" :class="['piece', drag ? 'drag' : '']"></div>
    <div v-if="isPuzzleComplete" class="puzzle-complete-indicator">✓</div>
  </div>
</template>

<script lang="ts">
import { defineComponent, computed } from "vue";

export default defineComponent({
  name: "Square",
  props: {
    isWhite: Boolean,
    piece: String,
    highlight: Boolean,
    drag: Boolean,
    isHighlighted: Boolean,
    isPuzzleComplete: Boolean,
  },
  setup(props) {
    const className = computed(() => {
      const classes = ["square"];

      // Base color
      if (props.isWhite) {
        classes.push("whiteSquare");
      } else {
        classes.push("blackSquare");
      }

      // Preview move highlighting - this should always work regardless of other highlights
      if (props.highlight) {
        if (props.piece) {
          classes.push("squareHighlightOccupied");
        } else {
          classes.push("squareHighlight");
        }
      }

      // Last move highlighting
      if (props.isHighlighted) {
        classes.push("lastMoveHighlight");
      }

      // Drag state
      if (props.drag) {
        classes.push("drag");
      }

      return classes.join(" ");
    });

    return { className };
  },
});
</script>

<style scoped>
.square {
  display: flex;
  justify-items: center;
}

.whiteSquare {
  background-color: #F0D9B7;
}

.blackSquare {
  background-color: #B58866;
}

.piece {
  background-repeat: no-repeat;
  background-position: center;
  background-size: contain;
  width: 10vmin;
  height: 10vmin;
}

/* Remove the background color to allow Chessboard's dot styling to work */
.squareHighlight {
  position: relative;
}

/* Only use a subtle outline for occupied squares */
.squareHighlightOccupied {
  border-radius: 3vmin;
}

.lastMoveHighlight {
  position: relative;
}

.lastMoveHighlight::after {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(76, 175, 80, 0.2);
  pointer-events: none;
}

.drag {
  cursor: grabbing;
}

.puzzle-complete-indicator {
  position: absolute;
  top: 0.5vmin;
  right: 0.5vmin;
  width: 3vmin;
  height: 3vmin;
  background-color: #4CAF50;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 2vmin;
  z-index: 20;
  box-shadow: 0 0 8px rgba(0, 0, 0, 0.5);
  animation: pulse-green 1.5s infinite;
}

@keyframes pulse-green {
  0% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.7);
  }

  70% {
    transform: scale(1);
    box-shadow: 0 0 0 10px rgba(76, 175, 80, 0);
  }

  100% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(76, 175, 80, 0);
  }
}

@media screen and (max-width: 800px) {
  .piece {
    width: 12.5vmin;
    height: 12.5vmin;
  }

  .puzzle-complete-indicator {
    width: 3vmin;
    height: 3vmin;
    font-size: 2vmin;
  }
}
</style>
