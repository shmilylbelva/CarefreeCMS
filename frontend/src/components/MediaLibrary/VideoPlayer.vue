<template>
  <div class="video-player" :class="{ fullscreen: isFullscreen }">
    <div class="player-wrapper" ref="playerWrapper">
      <video
        ref="videoRef"
        :src="src"
        :poster="poster"
        @loadedmetadata="handleLoadedMetadata"
        @timeupdate="handleTimeUpdate"
        @ended="handleEnded"
        @play="handlePlay"
        @pause="handlePause"
        @volumechange="handleVolumeChange"
        @click="togglePlay"
      ></video>

      <!-- 加载动画 -->
      <div v-if="loading" class="loading-overlay">
        <el-icon class="loading-icon" :size="48"><Loading /></el-icon>
      </div>

      <!-- 控制栏 -->
      <div class="controls" :class="{ show: showControls || !playing }">
        <!-- 进度条 -->
        <div class="progress-bar" @click="handleProgressClick">
          <div class="progress-loaded" :style="{ width: loadedPercent + '%' }"></div>
          <div class="progress-played" :style="{ width: playedPercent + '%' }"></div>
          <div class="progress-handle" :style="{ left: playedPercent + '%' }"></div>
        </div>

        <!-- 控制按钮 -->
        <div class="control-buttons">
          <div class="left-controls">
            <!-- 播放/暂停 -->
            <button class="control-btn" @click="togglePlay">
              <el-icon v-if="playing"><VideoPause /></el-icon>
              <el-icon v-else><VideoPlay /></el-icon>
            </button>

            <!-- 音量 -->
            <div class="volume-control">
              <button class="control-btn" @click="toggleMute">
                <el-icon v-if="volume === 0 || muted"><Muted /></el-icon>
                <el-icon v-else><Microphone /></el-icon>
              </button>
              <div class="volume-slider">
                <el-slider
                  v-model="volume"
                  :min="0"
                  :max="100"
                  vertical
                  height="80px"
                  @input="handleVolumeInput"
                />
              </div>
            </div>

            <!-- 时间 -->
            <span class="time">
              {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
            </span>
          </div>

          <div class="right-controls">
            <!-- 播放速度 -->
            <el-dropdown @command="handleSpeedChange">
              <button class="control-btn">
                {{ playbackRate }}x
              </button>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item command="0.5">0.5x</el-dropdown-item>
                  <el-dropdown-item command="0.75">0.75x</el-dropdown-item>
                  <el-dropdown-item command="1">1x (正常)</el-dropdown-item>
                  <el-dropdown-item command="1.25">1.25x</el-dropdown-item>
                  <el-dropdown-item command="1.5">1.5x</el-dropdown-item>
                  <el-dropdown-item command="2">2x</el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>

            <!-- 画质选择 -->
            <el-dropdown v-if="qualities.length > 0" @command="handleQualityChange">
              <button class="control-btn">
                {{ currentQuality }}
              </button>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item
                    v-for="q in qualities"
                    :key="q.value"
                    :command="q.value"
                  >
                    {{ q.label }}
                  </el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>

            <!-- 画中画 -->
            <button class="control-btn" @click="togglePictureInPicture" v-if="supportsPiP">
              <el-icon><Monitor /></el-icon>
            </button>

            <!-- 全屏 -->
            <button class="control-btn" @click="toggleFullscreen">
              <el-icon v-if="isFullscreen"><CloseBold /></el-icon>
              <el-icon v-else><FullScreen /></el-icon>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 缩略图预览 -->
    <div v-if="thumbnails.length > 0" class="thumbnails-preview">
      <div class="thumbnails-scroll">
        <div
          v-for="(thumb, index) in thumbnails"
          :key="index"
          class="thumbnail-item"
          @click="seekToThumbnail(thumb.time)"
        >
          <img :src="thumb.url" :alt="`${thumb.time}s`" />
          <span class="thumb-time">{{ formatTime(thumb.time) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import {
  VideoPlay,
  VideoPause,
  Microphone,
  Muted,
  FullScreen,
  CloseBold,
  Monitor,
  Loading
} from '@element-plus/icons-vue'

const props = defineProps({
  src: {
    type: String,
    required: true
  },
  poster: {
    type: String,
    default: ''
  },
  qualities: {
    type: Array,
    default: () => []
  },
  thumbnails: {
    type: Array,
    default: () => []
  },
  autoplay: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['play', 'pause', 'ended', 'timeupdate'])

// 视频元素
const videoRef = ref(null)
const playerWrapper = ref(null)

// 状态
const loading = ref(true)
const playing = ref(false)
const currentTime = ref(0)
const duration = ref(0)
const volume = ref(80)
const muted = ref(false)
const playbackRate = ref(1)
const currentQuality = ref('自动')
const isFullscreen = ref(false)
const showControls = ref(false)

let controlsTimer = null

// 计算属性
const playedPercent = computed(() => {
  return duration.value > 0 ? (currentTime.value / duration.value) * 100 : 0
})

const loadedPercent = computed(() => {
  if (!videoRef.value) return 0
  const buffered = videoRef.value.buffered
  if (buffered.length === 0) return 0
  return (buffered.end(buffered.length - 1) / duration.value) * 100
})

const supportsPiP = computed(() => {
  return document.pictureInPictureEnabled
})

// 事件处理
const handleLoadedMetadata = () => {
  loading.value = false
  duration.value = videoRef.value.duration
  if (props.autoplay) {
    play()
  }
}

const handleTimeUpdate = () => {
  currentTime.value = videoRef.value.currentTime
  emit('timeupdate', currentTime.value)
}

const handleEnded = () => {
  playing.value = false
  emit('ended')
}

const handlePlay = () => {
  playing.value = true
  emit('play')
}

const handlePause = () => {
  playing.value = false
  emit('pause')
}

const handleVolumeChange = () => {
  volume.value = videoRef.value.volume * 100
  muted.value = videoRef.value.muted
}

// 播放控制
const play = () => {
  videoRef.value?.play()
}

const pause = () => {
  videoRef.value?.pause()
}

const togglePlay = () => {
  if (playing.value) {
    pause()
  } else {
    play()
  }
}

const seek = (time) => {
  if (videoRef.value) {
    videoRef.value.currentTime = time
  }
}

const handleProgressClick = (e) => {
  const rect = e.currentTarget.getBoundingClientRect()
  const x = e.clientX - rect.left
  const percent = x / rect.width
  seek(duration.value * percent)
}

const seekToThumbnail = (time) => {
  seek(time)
  play()
}

// 音量控制
const handleVolumeInput = (value) => {
  if (videoRef.value) {
    videoRef.value.volume = value / 100
    videoRef.value.muted = value === 0
  }
}

const toggleMute = () => {
  if (videoRef.value) {
    videoRef.value.muted = !videoRef.value.muted
  }
}

// 播放速度
const handleSpeedChange = (rate) => {
  if (videoRef.value) {
    videoRef.value.playbackRate = parseFloat(rate)
    playbackRate.value = parseFloat(rate)
  }
}

// 画质切换
const handleQualityChange = (quality) => {
  const time = currentTime.value
  const wasPlaying = playing.value

  // 这里应该切换到不同画质的视频源
  // 实际实现需要根据后端提供的不同画质URL
  currentQuality.value = quality

  // 恢复播放位置
  setTimeout(() => {
    seek(time)
    if (wasPlaying) {
      play()
    }
  }, 100)
}

// 画中画
const togglePictureInPicture = async () => {
  try {
    if (document.pictureInPictureElement) {
      await document.exitPictureInPicture()
    } else {
      await videoRef.value.requestPictureInPicture()
    }
  } catch (error) {
    console.error('画中画切换失败', error)
  }
}

// 全屏
const toggleFullscreen = async () => {
  try {
    if (!document.fullscreenElement) {
      await playerWrapper.value.requestFullscreen()
      isFullscreen.value = true
    } else {
      await document.exitFullscreen()
      isFullscreen.value = false
    }
  } catch (error) {
    console.error('全屏切换失败', error)
  }
}

// 控制栏显示
const handleMouseMove = () => {
  showControls.value = true
  clearTimeout(controlsTimer)
  controlsTimer = setTimeout(() => {
    if (playing.value) {
      showControls.value = false
    }
  }, 3000)
}

// 格式化时间
const formatTime = (seconds) => {
  if (isNaN(seconds)) return '00:00'
  const h = Math.floor(seconds / 3600)
  const m = Math.floor((seconds % 3600) / 60)
  const s = Math.floor(seconds % 60)

  if (h > 0) {
    return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`
  }
  return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`
}

// 键盘快捷键
const handleKeyPress = (e) => {
  if (!videoRef.value) return

  switch (e.key) {
    case ' ':
      e.preventDefault()
      togglePlay()
      break
    case 'ArrowLeft':
      seek(Math.max(0, currentTime.value - 5))
      break
    case 'ArrowRight':
      seek(Math.min(duration.value, currentTime.value + 5))
      break
    case 'ArrowUp':
      e.preventDefault()
      handleVolumeInput(Math.min(100, volume.value + 10))
      break
    case 'ArrowDown':
      e.preventDefault()
      handleVolumeInput(Math.max(0, volume.value - 10))
      break
    case 'f':
      toggleFullscreen()
      break
    case 'm':
      toggleMute()
      break
  }
}

// 生命周期
onMounted(() => {
  document.addEventListener('keydown', handleKeyPress)
  playerWrapper.value?.addEventListener('mousemove', handleMouseMove)

  // 设置初始音量
  if (videoRef.value) {
    videoRef.value.volume = volume.value / 100
  }
})

onBeforeUnmount(() => {
  document.removeEventListener('keydown', handleKeyPress)
  playerWrapper.value?.removeEventListener('mousemove', handleMouseMove)
  clearTimeout(controlsTimer)
})

// 暴露方法
defineExpose({
  play,
  pause,
  seek,
  toggleFullscreen
})
</script>

<style scoped lang="scss">
.video-player {
  position: relative;
  width: 100%;
  background-color: #000;
  border-radius: 8px;
  overflow: hidden;

  &.fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 9999;
    border-radius: 0;
  }

  .player-wrapper {
    position: relative;
    width: 100%;
    height: 0;
    padding-bottom: 56.25%; // 16:9

    video {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
    }

    .loading-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: rgba(0, 0, 0, 0.8);

      .loading-icon {
        color: #fff;
        animation: rotate 1s linear infinite;
      }
    }

    .controls {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
      padding: 40px 20px 10px;
      opacity: 0;
      transition: opacity 0.3s;

      &.show {
        opacity: 1;
      }

      .progress-bar {
        position: relative;
        height: 4px;
        background-color: rgba(255, 255, 255, 0.3);
        cursor: pointer;
        margin-bottom: 10px;

        &:hover {
          height: 6px;
        }

        .progress-loaded {
          position: absolute;
          height: 100%;
          background-color: rgba(255, 255, 255, 0.5);
        }

        .progress-played {
          position: absolute;
          height: 100%;
          background-color: #409eff;
        }

        .progress-handle {
          position: absolute;
          top: 50%;
          width: 12px;
          height: 12px;
          background-color: #409eff;
          border-radius: 50%;
          transform: translate(-50%, -50%);
          opacity: 0;
          transition: opacity 0.3s;
        }

        &:hover .progress-handle {
          opacity: 1;
        }
      }

      .control-buttons {
        display: flex;
        justify-content: space-between;
        align-items: center;

        .left-controls,
        .right-controls {
          display: flex;
          align-items: center;
          gap: 10px;
        }

        .control-btn {
          background: none;
          border: none;
          color: #fff;
          cursor: pointer;
          padding: 5px 10px;
          font-size: 16px;
          transition: all 0.3s;

          &:hover {
            color: #409eff;
          }

          .el-icon {
            font-size: 20px;
          }
        }

        .volume-control {
          position: relative;

          .volume-slider {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            padding: 10px;
            background-color: rgba(0, 0, 0, 0.8);
            border-radius: 4px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
          }

          &:hover .volume-slider {
            opacity: 1;
            pointer-events: auto;
          }
        }

        .time {
          color: #fff;
          font-size: 13px;
          white-space: nowrap;
        }
      }
    }
  }

  .thumbnails-preview {
    margin-top: 15px;
    padding: 10px 0;
    background-color: #f5f7fa;
    border-radius: 8px;

    .thumbnails-scroll {
      display: flex;
      gap: 10px;
      overflow-x: auto;
      padding: 0 10px;

      .thumbnail-item {
        flex-shrink: 0;
        width: 120px;
        cursor: pointer;
        transition: transform 0.3s;

        &:hover {
          transform: scale(1.05);
        }

        img {
          width: 100%;
          height: 68px;
          object-fit: cover;
          border-radius: 4px;
        }

        .thumb-time {
          display: block;
          text-align: center;
          font-size: 12px;
          color: #606266;
          margin-top: 4px;
        }
      }
    }
  }
}

@keyframes rotate {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
