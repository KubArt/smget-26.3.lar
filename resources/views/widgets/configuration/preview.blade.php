<!-- ПРЕДПРОСМОТР -->
    <div class="block block-rounded sticky-top" style="top: 20px;">
        <div class="block-header block-header-default">
            <h3 class="block-title">Предпросмотр</h3>
            <div class="block-options">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-alt-secondary"
                            :class="previewMode === 'desktop' ? 'active' : ''"
                            @click="previewMode = 'desktop'">
                        <i class="fa fa-desktop me-1"></i> ПК
                    </button>
                    <button type="button" class="btn btn-alt-secondary"
                            :class="previewMode === 'mobile' ? 'active' : ''"
                            @click="previewMode = 'mobile'">
                        <i class="fa fa-mobile-alt me-1"></i> Мобильный
                    </button>
                    <button type="button" class="btn btn-alt-secondary"
                            :class="previewMode === 'tablet' ? 'active' : ''"
                            @click="previewMode = 'tablet'">
                        <i class="fa fa-tablet-alt me-1"></i> Планшет
                    </button>
                </div>
            </div>
        </div>
        <div class="block-content p-3 bg-body-dark">
            <div class="browser-mockup" :class="previewMode" id="browser-mockup">
                <div class="browser-header">
                    <div class="d-flex gap-1">
                        <span class="dot red"></span>
                        <span class="dot yellow"></span>
                        <span class="dot green"></span>
                    </div>
                    <div class="address-bar">
                        <i class="fa fa-lock me-1 text-success"></i> your-website.com
                    </div>
                    <div class="browser-controls">
                        <span class="badge bg-secondary" x-text="previewMode === 'desktop' ? '1920px' : (previewMode === 'tablet' ? '768px' : '375px')"></span>
                    </div>
                </div>
                <div class="browser-viewport" id="browser-viewport">
                    <div id="preview-host"></div>
                    <div class="site-placeholder">
                        <div class="hero-rect"></div>
                        <div class="p-3">
                            <div class="row g-3">
                                <div class="col-4"><div class="line"></div></div>
                                <div class="col-8"><div class="line w-75"></div></div>
                                <div class="col-12"><div class="line"></div></div>
                                <div class="col-12"><div class="line w-50"></div></div>
                                <div class="col-6"><div class="line"></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-2">
                <small class="text-muted" x-text="previewMode === 'desktop' ? '1920px × 500px' : (previewMode === 'tablet' ? '768px × 500px' : '375px × 500px')"></small>
            </div>
        </div>
    </div>


<style>
    .browser-mockup {
        border: 1px solid #d1d1d1;
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
        height: 800px;
        display: flex;
        flex-direction: column;
        margin: 0 auto;
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    /* Режимы ширины с плавным переходом */
    .browser-mockup.desktop {
        width: 100%;
        max-width: 100%;
    }
    .browser-mockup.tablet {
        width: 768px;
    }
    .browser-mockup.mobile {
        width: 375px;
    }

    .browser-header {
        background: #f1f1f1;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #e1e1e1;
        flex-shrink: 0;
    }

    .browser-header .dot {
        height: 10px;
        width: 10px;
        border-radius: 50%;
        margin-right: 6px;
    }
    .dot.red { background: #ff5f56; }
    .dot.yellow { background: #ffbd2e; }
    .dot.green { background: #27c93f; }

    .browser-header .address-bar {
        background: #fff;
        flex: 1;
        max-width: 400px;
        margin: 0 12px;
        border-radius: 4px;
        font-size: 11px;
        padding: 3px 10px;
        color: #666;
        text-align: center;
        border: 1px solid #e1e1e1;
    }

    .browser-controls {
        min-width: 60px;
        text-align: right;
    }

    .browser-viewport {
        position: relative;
        flex-grow: 1;
        background: #fff;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .site-placeholder {
        padding: 0;
        pointer-events: none;
    }
    .hero-rect {
        height: 160px;
        background: linear-gradient(135deg, #f0f2f5 0%, #e9ecef 100%);
        margin-bottom: 10px;
        width: 100%;
    }
    .line {
        height: 12px;
        background: #f0f2f5;
        border-radius: 6px;
        margin-bottom: 15px;
        width: 100%;
        background: linear-gradient(90deg, #f0f2f5 0%, #e9ecef 50%, #f0f2f5 100%);
        background-size: 200% auto;
        animation: shimmer 1.5s infinite;
    }

    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }

    #preview-host {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 100;
        pointer-events: none;
    }

    /* Адаптация для мобильных устройств */
    @media (max-width: 768px) {
        .browser-mockup.tablet,
        .browser-mockup.mobile {
            width: calc(100% - 32px);
        }
    }
</style>
