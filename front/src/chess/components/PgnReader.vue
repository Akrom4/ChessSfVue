<template>
  <div id="pgnBox" class="border border-light-pgn shadow-lg">
    <div id="pgnBoxInner">
      <div v-for="(chapter, index) in chapters" :key="index">
        <div class="movesContainer">
          <template v-for="(move, index) in chapter.Moves" :key="index">
            <span v-if="move.teamColor === 'w'" class="moveNumber">{{ move.moveNumber }}.&nbsp;</span>
            <span class="moves" :data-fen="move.position" @click="handleMoveClick(move)">
              {{ move.move }}&nbsp;
            </span>
            <div v-for="(comment, index) in renderComments(chapter.Comments, move.moveNumber, move.teamColor)"
              :key="index" class="comment">
              {{ comment }}
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, computed } from 'vue';

export default defineComponent({
  name: 'PgnReader',
  props: {
    pgnData: {
      type: Object,
      default: null
    },
    onMoveClick: {
      type: Function,
      required: true
    }
  },
  setup(props) {
    const chapters = computed(() => {
      if (!props.pgnData) return [];

      if (props.pgnData.chapter) {
        return props.pgnData.chapter || [];
      }

      if (props.pgnData.Moves) {
        return [props.pgnData];
      }

      return [];
    });

    const handleMoveClick = (move: any) => {
      props.onMoveClick(move, move.position);
    };

    const renderComments = (comments: any[], moveNumber: number, color: string) => {
      if (!comments) return [];

      return comments
        .filter(comment => comment.moveNumber === moveNumber && comment.teamColor === color)
        .map(comment => String(comment.text).replace(/\[%cal .*?\]/g, ''));
    };

    return {
      chapters,
      handleMoveClick,
      renderComments
    };
  }
});
</script>

<style scoped>
#pgnBox {
  border-radius: 1vmin;
  width: 50vmin;
  height: 80vmin;
  display: flex;
  position: relative;
  top: 0;
  flex-direction: column;
  padding: 2vmin;
  margin-left: 2vmin;
  font-family: "Roboto";
  overflow-y: auto;
  background-color: #fff;
}

#pgnBoxInner {
  min-height: 0;
  overflow-y: auto;
}

.movesContainer {
  margin-bottom: 1rem;
}

.moveNumber {
  color: #666;
  font-weight: bold;
}

.moves {
  color: #214a7c;
  font-weight: bold;
  padding: 0.25rem 0.5rem;
  margin: 0.125rem;
  cursor: pointer;
  display: inline-block;
  border-radius: 0.25rem;
}

.moves:hover {
  background-color: #f0f0f0;
}

.comment {
  margin: 0.5rem 0;
  padding: 0.5rem;
  background-color: #f5f5f5;
  border-left: 0.25rem solid #214a7c;
  font-size: 0.875rem;
  line-height: 1.4;
}

@media (max-width: 768px) {
  #pgnBox {
    width: 100%;
    max-width: 100%;
    height: auto;
    max-height: 50vh;
    margin-left: 0;
    margin-top: 1rem;
    padding: 1rem;
  }
}
</style>