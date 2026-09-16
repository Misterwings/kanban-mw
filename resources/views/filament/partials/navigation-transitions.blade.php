<style>
    .fi-page {
        animation: panel-page-enter .2s ease-out both;
    }

    @keyframes panel-page-enter {
        from {
            opacity: .01;
        }

        to {
            opacity: 1;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .fi-page {
            animation: none;
        }
    }
</style>
