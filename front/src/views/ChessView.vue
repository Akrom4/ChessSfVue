<template>
    <div id="chess-view">
        <!-- Use the chessboard component here -->
        <ChessApp />
    </div>
</template>

<script>
import ChessApp from '../chess/ChessApp.vue'; // Adjust the path as necessary

export default {
    name: 'ChessView',
    components: {
        ChessApp
    }
};
</script>

<style scoped>
#chess-view {
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>
