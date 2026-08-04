# 웹 시티 빌더 게임 기획안

# 《어느 날 눈떠보니 문제투성이 마왕국의 마왕이 되었다》

## 1. 프로젝트 개요

### 공식 제목

**어느 날 눈떠보니 문제투성이 마왕국의 마왕이 되었다**

### 축약 제목

**문제투성이 마왕국**

공식 제목은 타이틀 화면과 홍보 이미지에 사용하고, 브라우저 제목·메뉴·아이콘·URL 등에는 축약 제목을 사용한다.

### 장르

- 캐주얼 시티 빌더
- 도시 경영 시뮬레이션
- 생산 체인 관리
- 주민 수집 및 배치
- 사건 선택형 시뮬레이션
- 장기 성장형 웹게임

### 플랫폼

- PC 웹 브라우저 우선
- 태블릿 대응
- 모바일 가로 화면은 추후 지원
- 별도 설치 없이 브라우저에서 실행
- 회원 계정과 서버 저장 기능 제공
- 인터넷 연결을 기본 전제로 운영

### 서비스 주소

- 게임 및 인증: `https://dk.moonscenty.me`
- API: `https://dk.moonscenty.me/api/`
- 별도의 API 서브도메인을 두지 않고 동일 도메인에서 운영한다.

---

## 2. 핵심 설계 방향

이 게임은 처음부터 **Laravel + MariaDB 기반의 서버 권한형 구조**로 개발한다.

클라이언트는 게임 화면을 표시하고 사용자의 입력을 전달하지만, 자원·건물·주민·퀘스트·사건·연구 등 실제 게임 데이터의 최종 결정권은 서버가 가진다.

### 기본 원칙

1. 브라우저에는 영구적인 게임 세이브를 저장하지 않는다.
2. IndexedDB를 게임 데이터 저장소로 사용하지 않는다.
3. `localStorage`에도 자원, 건물, 주민 등 핵심 게임 데이터를 저장하지 않는다.
4. 클라이언트는 현재 화면을 표시하기 위한 메모리 상태만 보유한다.
5. 모든 중요한 행동은 Laravel API를 통해 서버에 요청한다.
6. Laravel은 행동의 유효성을 검증한 뒤 MariaDB에 반영한다.
7. 서버가 승인한 결과만 실제 게임 상태로 인정한다.
8. 브라우저를 조작해 자원 값을 변경해도 서버 데이터에는 영향을 주지 못하게 한다.
9. MariaDB에는 최종 게임 상태와 행동 이력을 함께 기록한다.
10. 모든 쓰기 작업은 트랜잭션과 리비전 검사를 사용한다.

### 전체 흐름

```text
사용자 입력
    ↓
Vue / PixiJS에서 명령 생성
    ↓
Laravel API로 명령 전송
    ↓
인증 및 요청 형식 검사
    ↓
현재 월드와 자원 상태 잠금
    ↓
게임 규칙 검증
    ↓
MariaDB 트랜잭션 처리
    ↓
월드 revision 증가
    ↓
변경 결과를 클라이언트에 반환
    ↓
Pinia와 PixiJS 화면 갱신
```

클라이언트는 MariaDB에 직접 접속하지 않는다. 모든 데이터 접근은 Laravel을 통해서만 수행한다.

---

## 3. 게임 콘셉트

플레이어는 어느 날 갑자기 마왕으로 임명된다.

그러나 물려받은 마왕국은 거대한 제국이 아니라 다음과 같은 상태다.

- 마왕성 대부분이 무너져 있다.
- 국고에는 금화가 거의 없다.
- 주민들이 일을 제대로 하지 않는다.
- 생산 시설이 모두 고장 나 있다.
- 인간 모험가들이 수시로 침입한다.
- 마족 간부들이 서로 책임을 떠넘긴다.
- 주변 마왕국이 플레이어의 영토를 노리고 있다.

플레이어는 폐허를 정리하고 건물을 세우며, 주민에게 직업을 배정하고 생산 체인을 연결해 마왕국을 재건한다.

게임의 핵심은 완벽한 도시를 만드는 것이 아니다.

**문제를 일으키는 주민들과 사고가 끊이지 않는 도시를 어떻게든 성장시키는 것**이 핵심이다.

---

## 4. 핵심 재미

### 4.1 눈에 보이는 빠른 성장

플레이 시작 직후부터 도시 화면이 빠르게 변화해야 한다.

- 폐허가 제거된다.
- 새로운 길이 생긴다.
- 건물이 완성된다.
- 주민이 도시로 이주한다.
- 생산품이 건물 사이를 이동한다.
- 마왕성이 단계적으로 복구된다.
- 안개가 걷히며 새로운 구역이 열린다.

숫자만 증가하는 방식이 아니라 도시 화면 자체가 계속 변화하도록 만든다.

### 4.2 연속 보상

플레이어는 짧은 주기로 보상을 받는다.

- 생산 완료
- 퀘스트 완료
- 건물 레벨 상승
- 주민 성장
- 새로운 건물 발견
- 새로운 종족 영입
- 지역 확장
- 사건 해결
- 업적 달성

한 번의 행동이 다음 행동으로 자연스럽게 이어지도록 설계한다.

### 4.3 문제투성이 주민

주민은 생산량을 올려주는 단순한 카드가 아니다.

도시 안을 직접 돌아다니며 일하고, 쉬고, 식사하고, 다투고, 사고를 일으킨다.

예시:

- 슬라임이 창고의 물약을 마신다.
- 고블린이 급여 인상을 요구한다.
- 오우거가 출입문이 작다며 건물을 부순다.
- 리치가 묘지 주민을 멋대로 부활시킨다.
- 임프가 폭발물을 잘못 배송한다.
- 뱀파이어가 낮 근무를 거부한다.

이러한 사건은 단순한 손해가 아니라 새로운 선택, 후속 사건, 보상과 콘텐츠로 이어진다.

### 4.4 도시 자동화

초기에는 플레이어가 직접 자원을 회수하고 주민을 배치한다.

도시가 성장하면 다음과 같은 자동화 기능이 열린다.

- 자동 수거 골렘
- 임프 운송대
- 마법 물류 포털
- 자동 생산 명령서
- 마나 철도
- 도시 관리 비서

초반에는 직접 조작하는 재미를 주고, 후반에는 도시를 설계하고 최적화하는 재미로 전환한다.

---

## 5. 핵심 플레이 루프

### 기본 루프

**건설 → 주민 배치 → 생산 → 자원 회수 → 업그레이드 → 사건 발생 → 문제 해결 → 지역 확장**

### 짧은 루프: 10초~1분

- 생산품 수집
- 주민 배치
- 작은 건물 건설
- 퀘스트 보상 수령
- 사고 처리
- 간단한 업그레이드

### 중간 루프: 3분~15분

- 생산 체인 완성
- 새로운 주민 영입
- 건물 등급 상승
- 도시 레벨 상승
- 신규 구역 개방
- 모험가 습격 방어

### 장기 루프: 수일~수주

- 마왕성 완전 복구
- 도시 전문화
- 새로운 시대 진입
- 주변 지역 정복
- 희귀 주민 수집
- 대형 랜드마크 건설
- 마왕국의 결말 선택

---

## 6. 주요 게임 시스템

### 6.1 도시 맵

도시는 타일 기반으로 구성한다.

- 초기 맵은 `20×20` 타일로 시작한다.
- 확장을 통해 주변 구역이 추가된다.
- 각 타일은 고유한 좌표를 가진다.
- 서버는 타일 점유 상태와 설치 가능 여부를 최종 검증한다.

각 타일에는 다음 중 하나가 배치될 수 있다.

- 건물
- 길
- 장식물
- 자원 지형
- 장애물
- 랜드마크
- 방어 시설

### 6.2 건물 배치

건물은 마우스로 선택해 원하는 타일에 설치한다.

지원 기능:

- 배치 미리 보기
- 회전
- 이동
- 철거
- 배치 취소
- 유효 타일 표시
- 건물 영향 범위 표시

클라이언트는 설치 가능 위치를 미리 표시하지만, 최종 설치 가능 여부는 서버가 판단한다.

#### 건설 요청 처리

```text
플레이어가 건물 위치 선택
    ↓
클라이언트가 반투명 미리 보기 표시
    ↓
서버에 건설 명령 전송
    ↓
서버가 비용·좌표·해금 조건 검증
    ↓
성공 시 자원 차감 및 건물 생성
    ↓
실패 시 미리 보기 제거와 오류 표시
```

서버 응답 전에는 건물을 확정된 상태로 표시하지 않는다.

### 6.3 건물 시너지

특정 건물을 가까이 배치하면 추가 효과가 발생한다.

| 조합 | 효과 |
|---|---|
| 농장 + 제빵소 | 식량 생산 증가 |
| 광산 + 대장간 | 장비 생산 속도 증가 |
| 마나 우물 + 연구소 | 연구 시간 감소 |
| 시장 + 창고 | 판매 효율 증가 |
| 주점 + 모험가 길드 | 방문객 증가 |
| 묘지 + 흑마법 연구소 | 언데드 생성 |
| 병영 + 감시탑 | 방어력 증가 |

시너지 계산은 서버가 건물 좌표를 기준으로 수행한다.

클라이언트는 서버가 반환한 시너지 결과를 빛, 화살표, 운송 경로 등의 효과로 표현한다.

### 6.4 건물 성장

건물은 다음 단계로 성장한다.

1. 폐허
2. 임시 시설
3. 정식 시설
4. 전문 시설
5. 마왕국 핵심 시설
6. 전설 등급 시설

등급이 오르면 다음 요소가 변경된다.

- 외형
- 크기
- 애니메이션
- 생산품
- 생산 속도
- 배치 가능한 주민 수
- 특수 능력

업그레이드 비용, 선행 조건, 소요 시간은 서버에서 검증한다.

### 6.5 자원과 생산 체인

#### 기본 자원

- 금화
- 식량
- 목재
- 광석
- 마나

#### 가공 자원 예시

- 광석 → 철괴 → 무기
- 목재 → 판자 → 가구
- 밀 → 밀가루 → 빵
- 마나 → 마법석 → 마법 장비
- 몬스터 가죽 → 가죽 → 방어구
- 영혼 조각 → 저주석 → 흑마법 도구

각 생산 건물에는 다음 정보가 존재한다.

- 생산 품목
- 기본 생산량
- 생산 시간
- 최대 보관량
- 필요 주민
- 소비 자원
- 생산 보너스
- 인접 건물 효과
- 마지막 서버 처리 시각
- 다음 생산 완료 시각

자원 보유량과 생산 결과는 MariaDB에 저장된 서버 데이터를 기준으로 계산한다.

### 6.6 주민

#### 기본 주민 종족

- **슬라임:** 유지비가 낮고 어떤 시설에도 배치할 수 있지만 생산 능력이 낮다.
- **고블린:** 생산과 상업에 강하지만 급여와 불만 관리가 필요하다.
- **오우거:** 건설과 채광에 강하지만 느리고 시설을 파손할 수 있다.
- **임프:** 이동과 운송에 강하지만 배송 사고가 발생할 수 있다.
- **뱀파이어:** 상업과 세금 수입에 강하지만 낮에는 효율이 감소한다.
- **리치:** 연구와 마법에 강하지만 주변 주민에게 악영향을 줄 수 있다.

#### 주민 능력치

- 생산
- 건설
- 연구
- 전투
- 이동
- 매력
- 충성도

#### 주민 상태

- 근무 중
- 이동 중
- 휴식 중
- 식사 중
- 불만
- 부상
- 사고 발생
- 축제 참여
- 파업

주민의 실제 소속, 배치 건물, 능력치, 충성도, 부상 상태는 서버에 저장한다.

걷기 애니메이션과 화면상의 세부 이동 경로는 클라이언트가 표현할 수 있지만, 주민의 최종 배치와 작업 결과는 서버가 결정한다.

### 6.7 사건

도시는 일정 조건에 따라 작은 사건이나 대형 사건을 발생시킨다.

#### 작은 사건

- 주민 다툼
- 생산 시설 고장
- 창고 도난
- 음식 부족
- 배송 실수
- 작은 화재

#### 대형 사건

- 고블린 총파업
- 마법 연구소 폭발
- 인간 영웅 습격
- 드래곤의 금고 점거
- 언데드 대량 부활
- 주변 마왕의 선전포고

사건의 발생 여부, 선택 가능 항목, 보상과 불이익은 서버에서 관리한다.

클라이언트가 존재하지 않는 선택지나 이미 끝난 사건을 전송하면 서버가 거부한다.

### 6.8 퀘스트

퀘스트는 다음 네 종류로 구성한다.

- **안내 퀘스트:** 건설, 주민 배치, 생산 등 기본 기능 학습
- **일일 퀘스트:** 짧은 반복 목표와 보상
- **성장 퀘스트:** 인구, 생산 체인, 마왕성 복구, 습격 방어
- **이야기 퀘스트:** 전임 마왕의 실종, 빈 국고, 지하 봉인, 플레이어가 선택된 이유

퀘스트 진행도는 클라이언트가 직접 증가시키지 않는다.

서버에서 건설, 생산, 사건 해결 등의 실제 행동이 완료될 때 퀘스트 진행도를 함께 갱신한다.

---

## 7. 서버 시간 기반 게임 진행

### 7.1 서버 기준 시간

모든 생산, 연구, 탐험, 건설 시간은 서버 시간을 기준으로 계산한다.

클라이언트가 전달하는 현재 시각은 게임 계산에 사용하지 않는다.

주요 시간 컬럼:

- `started_at`
- `finishes_at`
- `last_processed_at`
- `last_active_at`
- `server_time`

### 7.2 생산 처리 방식

MariaDB의 값을 매초 갱신하지 않는다.

건물별로 생산 시작 시각과 마지막 처리 시각을 저장하고, 다음 상황에서 경과 시간을 계산한다.

- 사용자가 월드에 접속했을 때
- 생산품을 수거할 때
- 건물을 업그레이드할 때
- 주민 배치를 변경할 때
- 서버 스케줄 작업이 실행될 때
- 오프라인 결과를 계산할 때

예시:

```text
현재 서버 시각 - 마지막 처리 시각
    ↓
완료된 생산 횟수 계산
    ↓
창고 최대 용량 적용
    ↓
생산 결과 반영
    ↓
마지막 처리 시각 갱신
```

이 방식으로 데이터베이스를 매초 갱신하지 않고도 서버 기준 생산을 구현한다.

### 7.3 오프라인 성장

플레이어가 접속하지 않은 동안에도 일부 생산과 시간이 진행된다.

- 서버 시간을 기준으로 계산한다.
- 초기 오프라인 생산 상한은 8시간으로 설정한다.
- 창고 최대 용량까지만 생산한다.
- 연구와 탐험은 남은 시간을 차감한다.
- 사건은 무한히 누적하지 않고 최대 개수를 제한한다.
- 비정상적으로 긴 오프라인 시간은 서버가 제한한다.

재접속 시 다음 순서로 결과를 보여준다.

1. 서버가 오프라인 진행을 계산한다.
2. 변경된 자원과 완료된 작업을 MariaDB에 반영한다.
3. 클라이언트가 최신 월드 상태를 받는다.
4. 도시가 다시 움직이기 시작한다.
5. 생산 결과와 오프라인 보고서를 연출한다.

---

## 8. 기술 구성

### 8.1 프론트엔드 기술 스택

- Vite
- Vue 3
- TypeScript
- Pinia
- Vue Router
- PixiJS 8
- Axios
- Vitest
- ESLint
- Prettier

IndexedDB는 사용하지 않는다.

게임 데이터 저장을 위한 `localStorage`도 사용하지 않는다.

### 8.2 백엔드 기술 스택

- Laravel 13
- PHP 8.4
- MariaDB
- InnoDB
- Laravel Sanctum
- Nginx
- Composer
- PHPUnit 또는 Pest
- Redis는 캐시, 큐, 속도 제한이 필요할 때 선택 적용

### 8.3 역할 분리

#### Vue

- 로그인과 회원 가입
- 메인 메뉴
- 건설 메뉴
- 주민 목록
- 퀘스트와 사건 창
- 설정
- 동기화 상태
- 오류 메시지와 팝업

#### PixiJS

- 도시 맵
- 건물과 주민
- 길과 이동 경로
- 파티클과 날씨
- 건설 애니메이션
- 자원 획득 연출
- 습격 전투
- 카메라 이동과 확대·축소

#### Pinia

- 서버에서 받은 현재 월드 상태
- 화면에 표시할 자원과 건물
- 주민과 퀘스트
- 진행 중인 서버 명령
- UI 상태
- 서버 revision
- 서버 시간과 동기화 상태

Pinia 데이터는 브라우저 메모리에만 유지한다.

페이지를 새로 고치면 MariaDB에 저장된 최신 상태를 API로 다시 불러온다.

#### Laravel

- 사용자 인증
- 게임 명령 수신
- 게임 규칙 검증
- MariaDB 트랜잭션 처리
- 월드 revision 관리
- 오프라인 진행 계산
- 퀘스트와 사건 처리
- 행동 로그와 백업 관리
- 비정상 요청 차단

#### MariaDB

- 사용자와 월드
- 자원
- 타일과 건물
- 주민
- 생산 상태
- 퀘스트와 사건
- 연구와 탐험
- 명령 처리 기록
- 감사 로그
- 백업 스냅샷

---

## 9. 프론트엔드 구조

```text
frontend/
├─ public/
├─ src/
│  ├─ app/
│  │  ├─ router/
│  │  └─ bootstrap/
│  ├─ assets/
│  │  ├─ buildings/
│  │  ├─ characters/
│  │  ├─ effects/
│  │  ├─ terrain/
│  │  ├─ ui/
│  │  └─ sounds/
│  ├─ components/
│  │  ├─ common/
│  │  ├─ game/
│  │  └─ layout/
│  ├─ views/
│  │  ├─ LoginView.vue
│  │  ├─ RegisterView.vue
│  │  ├─ MainMenuView.vue
│  │  └─ GameView.vue
│  ├─ game/
│  │  ├─ core/
│  │  ├─ entities/
│  │  ├─ systems/
│  │  ├─ simulation/
│  │  └─ renderer/
│  ├─ stores/
│  │  ├─ authStore.ts
│  │  ├─ worldStore.ts
│  │  ├─ resourceStore.ts
│  │  ├─ buildingStore.ts
│  │  ├─ residentStore.ts
│  │  ├─ questStore.ts
│  │  ├─ eventStore.ts
│  │  └─ syncStore.ts
│  ├─ services/
│  │  ├─ api/
│  │  ├─ auth/
│  │  ├─ commands/
│  │  └─ synchronization/
│  ├─ types/
│  ├─ utils/
│  ├─ App.vue
│  └─ main.ts
├─ package.json
└─ vite.config.ts
```

### 중요 설계 원칙

PixiJS 객체를 Pinia Store 안에 직접 저장하지 않는다.

Pinia에는 서버에서 받은 순수한 게임 데이터만 저장하고, PixiJS 렌더러가 Store 변화를 감지해 화면을 갱신한다.

```text
MariaDB의 게임 상태
        ↓
Laravel API 응답
        ↓
Pinia의 순수 데이터
        ↓
PixiJS Renderer
        ↓
Sprite 생성 및 갱신
```

---

## 10. 백엔드 구조

```text
backend/
├─ app/
│  ├─ Domain/
│  │  └─ Game/
│  │     ├─ Commands/
│  │     ├─ Handlers/
│  │     ├─ Rules/
│  │     ├─ Services/
│  │     ├─ DTOs/
│  │     └─ Exceptions/
│  ├─ Http/
│  │  ├─ Controllers/
│  │  │  └─ Api/
│  │  │     └─ V1/
│  │  ├─ Requests/
│  │  │  └─ Api/
│  │  │     └─ V1/
│  │  └─ Resources/
│  │     └─ Api/
│  │        └─ V1/
│  ├─ Models/
│  ├─ Policies/
│  ├─ Jobs/
│  ├─ Console/
│  │  └─ Commands/
│  └─ Support/
├─ database/
│  ├─ factories/
│  ├─ migrations/
│  └─ seeders/
├─ routes/
│  ├─ api.php
│  └─ web.php
├─ tests/
│  ├─ Feature/
│  └─ Unit/
└─ composer.json
```

### 명령 처리 예시

```text
PlaceBuildingCommand
    ↓
PlaceBuildingRequest 검증
    ↓
PlaceBuildingHandler
    ↓
WorldRule / BuildingRule 검사
    ↓
MariaDB 트랜잭션
    ↓
건물 생성 + 자원 차감 + 로그 기록
    ↓
WorldResource 응답
```

---

## 11. MariaDB 데이터베이스 설계

전체 게임 상태를 하나의 수정 가능한 JSON 세이브 파일로 저장하지 않는다.

핵심 데이터는 관계형 테이블로 분리하고 외래 키, 고유 인덱스, 체크 조건과 트랜잭션으로 보호한다.

JSON 컬럼은 사건 세부 정보, 로그 데이터, 백업 스냅샷처럼 구조가 유동적인 보조 데이터에만 제한적으로 사용한다.

### 11.1 계정과 월드

#### `users`

- `id`
- `name`
- `email`
- `password`
- `created_at`
- `updated_at`

#### `guest_sessions`

- `id`
- `session_token_hash`
- `expires_at`
- `converted_user_id`
- `created_at`

게스트 플레이도 서버에 월드를 생성한다.

회원 가입 시 게스트 월드의 소유자를 새 사용자 계정으로 이전한다.

#### `game_worlds`

- `id`
- `user_id`
- `guest_session_id`
- `name`
- `city_level`
- `population`
- `current_era`
- `revision`
- `last_processed_at`
- `last_active_at`
- `created_at`
- `updated_at`

`user_id`와 `guest_session_id` 중 하나가 월드 소유자를 나타낸다.

### 11.2 자원

#### `world_resources`

- `id`
- `game_world_id`
- `resource_type`
- `amount`
- `capacity`
- `updated_at`

고유 인덱스:

```text
UNIQUE(game_world_id, resource_type)
```

금화, 식량, 목재, 광석, 마나를 월드별로 관리한다.

자원 값에는 정수형을 사용하고 음수가 되지 않도록 서버와 DB 양쪽에서 검증한다.

### 11.3 맵과 건물

#### `world_areas`

- `id`
- `game_world_id`
- `area_type`
- `is_unlocked`
- `unlocked_at`

#### `world_tiles`

- `id`
- `game_world_id`
- `area_id`
- `x`
- `y`
- `terrain_type`
- `is_buildable`

고유 인덱스:

```text
UNIQUE(game_world_id, x, y)
```

#### `buildings`

- `id`
- `game_world_id`
- `building_type_id`
- `x`
- `y`
- `rotation`
- `level`
- `state`
- `started_at`
- `finishes_at`
- `last_processed_at`
- `created_at`
- `updated_at`

#### `building_definitions`

- `id`
- `code`
- `name`
- `width`
- `height`
- `max_level`
- `base_build_time`
- `is_active`

#### `building_level_definitions`

- `id`
- `building_type_id`
- `level`
- `build_cost`
- `production_time`
- `production_amount`
- `storage_capacity`
- `worker_capacity`

정적인 게임 설정은 정의 테이블 또는 버전 관리된 서버 설정으로 관리한다.

### 11.4 생산

#### `building_productions`

- `id`
- `building_id`
- `recipe_id`
- `stored_amount`
- `started_at`
- `last_processed_at`
- `next_completion_at`
- `is_active`

#### `production_recipes`

- `id`
- `code`
- `input_resource_type`
- `input_amount`
- `output_resource_type`
- `output_amount`
- `duration_seconds`

### 11.5 주민

#### `residents`

- `id`
- `game_world_id`
- `resident_type_id`
- `name`
- `level`
- `experience`
- `loyalty`
- `health_state`
- `current_state`
- `assigned_building_id`
- `created_at`
- `updated_at`

#### `resident_definitions`

- `id`
- `code`
- `race`
- `base_production`
- `base_construction`
- `base_research`
- `base_combat`
- `base_movement`
- `base_charm`

#### `resident_traits`

- `id`
- `resident_id`
- `trait_type`
- `value`

### 11.6 퀘스트와 사건

#### `quests`

퀘스트 정의

#### `world_quests`

- `id`
- `game_world_id`
- `quest_id`
- `status`
- `progress`
- `completed_at`
- `rewarded_at`

#### `world_events`

- `id`
- `game_world_id`
- `event_type`
- `status`
- `payload`
- `occurred_at`
- `expires_at`
- `resolved_at`

#### `world_event_choices`

- `id`
- `world_event_id`
- `choice_code`
- `selected_at`
- `result_payload`

### 11.7 연구와 탐험

#### `world_research`

- `id`
- `game_world_id`
- `research_type`
- `level`
- `started_at`
- `finishes_at`
- `completed_at`

#### `expeditions`

- `id`
- `game_world_id`
- `expedition_type`
- `status`
- `started_at`
- `returns_at`
- `result_payload`

### 11.8 명령과 감사 로그

#### `game_commands`

- `id`
- `game_world_id`
- `user_id`
- `command_id`
- `command_type`
- `base_revision`
- `status`
- `request_payload`
- `response_payload`
- `created_at`
- `completed_at`

고유 인덱스:

```text
UNIQUE(game_world_id, command_id)
```

동일한 명령이 네트워크 재시도로 두 번 전송되어도 한 번만 처리하도록 한다.

#### `game_action_logs`

- `id`
- `game_world_id`
- `user_id`
- `action_type`
- `target_type`
- `target_id`
- `before_payload`
- `after_payload`
- `ip_address`
- `created_at`

운영 확인과 비정상 행동 분석에 사용한다.

### 11.9 백업

#### `world_snapshots`

- `id`
- `game_world_id`
- `revision`
- `snapshot_type`
- `state_json`
- `checksum`
- `created_at`

스냅샷은 실제 플레이 데이터의 원본이 아니라 복구용 백업이다.

다음 시점에 생성한다.

- 시대 전환 전
- 대형 업데이트 전
- 월드 초기화 전
- 관리자 수동 백업
- 일정 주기의 운영 백업

---

## 12. 서버 명령 처리와 동시성

### 12.1 명령 요청 형식

```json
{
  "commandId": "8fb4db92-2ac1-44aa-8fd0-8a119233e879",
  "baseRevision": 25,
  "type": "building.place",
  "payload": {
    "buildingType": "farm",
    "x": 4,
    "y": 7,
    "rotation": 0
  }
}
```

### 12.2 처리 순서

1. 로그인 또는 게스트 세션을 확인한다.
2. 사용자가 해당 월드의 소유자인지 확인한다.
3. `commandId` 중복 여부를 확인한다.
4. 데이터 형식과 허용된 값인지 검사한다.
5. MariaDB 트랜잭션을 시작한다.
6. 대상 월드 행을 잠근다.
7. 현재 `revision`과 `baseRevision`을 비교한다.
8. 필요한 자원, 좌표, 선행 조건을 검사한다.
9. 게임 데이터를 변경한다.
10. 행동 로그와 명령 결과를 기록한다.
11. 월드 `revision`을 증가시킨다.
12. 트랜잭션을 커밋한다.
13. 변경된 데이터와 새 revision을 반환한다.

### 12.3 응답 예시

```json
{
  "success": true,
  "revision": 26,
  "serverTime": "2026-08-05T08:00:00+09:00",
  "changes": {
    "resources": {
      "wood": 850,
      "gold": 420
    },
    "buildings": [
      {
        "id": 301,
        "type": "farm",
        "x": 4,
        "y": 7,
        "level": 1,
        "state": "constructing"
      }
    ]
  }
}
```

### 12.4 충돌 처리

클라이언트가 보유한 revision보다 서버 revision이 최신이면 명령을 즉시 적용하지 않는다.

서버는 `409 Conflict`와 최신 revision을 반환한다.

클라이언트는 최신 변경 내용을 다시 받아 Pinia를 갱신한 뒤 필요한 경우 명령을 다시 시도한다.

### 12.5 오류 코드

- `401 Unauthorized`: 인증되지 않음
- `403 Forbidden`: 월드 접근 권한 없음
- `409 Conflict`: revision 충돌
- `422 Unprocessable Entity`: 게임 규칙 또는 입력값 위반
- `429 Too Many Requests`: 요청 횟수 초과

---

## 13. 동기화와 네트워크 장애 처리

### 13.1 자동 저장 개념 제거

서버 권한형 구조에서는 별도의 세이브 버튼이나 주기적 전체 저장을 기본으로 하지 않는다.

건설, 업그레이드, 자원 회수, 주민 배치, 사건 선택 등 각 행동이 성공하는 순간 MariaDB에 즉시 저장된다.

따라서 UI에는 다음 상태를 표시한다.

- 동기화 중
- 서버 반영 완료
- 연결 끊김
- 재연결 중
- 데이터 충돌
- 서버 오류

### 13.2 네트워크가 끊긴 경우

인터넷 연결이 끊기면 게임 진행을 일시 정지한다.

- 건설 확정 불가
- 자원 수거 불가
- 주민 배치 변경 불가
- 사건 선택 불가
- 연구 시작 불가

도시 애니메이션은 잠시 유지할 수 있지만 실제 결과는 생성하지 않는다.

재연결 후 서버에서 최신 상태를 다시 받아 화면을 복원한다.

### 13.3 메모리 상태

Pinia에는 현재 표시 중인 데이터를 보관하지만 새로고침하면 사라진다.

새로고침 후 다음 순서로 복원한다.

```text
세션 쿠키 확인
    ↓
현재 월드 조회
    ↓
MariaDB 기반 전체 상태 요청
    ↓
Pinia 초기화
    ↓
PixiJS 도시 재구성
```

### 13.4 변경분 동기화

초기 MVP에서는 월드 전체 상태를 불러올 수 있다.

데이터가 커지면 revision 이후의 변경분만 받는 API를 추가한다.

```text
GET /api/v1/worlds/{world}/changes?afterRevision=25
```

실시간 다중 접속이 필요해지면 이후 WebSocket 또는 Server-Sent Events를 검토한다.

---

## 14. 인증과 보안

### 14.1 인증

Vue SPA와 Laravel API는 동일 도메인에서 Sanctum 세션 쿠키 인증을 사용한다.

- 로그인 세션은 서버에서 관리한다.
- 비밀번호는 해시로 저장한다.
- 상태 변경 요청에는 CSRF 보호를 적용한다.
- 월드 API마다 소유권을 검사한다.
- 게스트도 서버 발급 세션으로 식별한다.

### 14.2 데이터 변조 대응

클라이언트가 다음 값을 변경해 전송하더라도 서버는 그대로 신뢰하지 않는다.

- 현재 자원
- 생산량
- 건물 가격
- 업그레이드 비용
- 주민 능력치
- 퀘스트 진행도
- 사건 보상
- 오프라인 시간
- 연구 완료 시각

클라이언트는 원하는 행동과 대상만 전달한다.

실제 비용과 결과는 서버의 게임 정의와 현재 MariaDB 데이터를 기준으로 계산한다.

잘못된 요청 예시:

```json
{
  "type": "building.place",
  "payload": {
    "buildingType": "legendary_castle",
    "cost": 0,
    "reward": 99999999
  }
}
```

이 요청에서 `cost`와 `reward`는 무시하거나 허용하지 않는다.

서버는 `buildingType`만 확인하고 실제 비용과 해금 조건을 서버 설정에서 조회한다.

### 14.3 데이터베이스 보호

- MariaDB 포트를 외부 인터넷에 직접 공개하지 않는다.
- Laravel 서버만 MariaDB에 접속하게 한다.
- 애플리케이션 전용 DB 계정을 사용한다.
- 필요한 권한만 부여한다.
- 운영 DB와 개발 DB를 분리한다.
- 정기 백업을 수행한다.
- 모든 핵심 테이블은 InnoDB를 사용한다.
- 외래 키와 고유 인덱스를 적극적으로 사용한다.
- 중요한 변경은 트랜잭션으로 처리한다.

### 14.4 요청 제한과 감사

- 사용자별 요청 횟수 제한
- 월드별 명령 처리 제한
- 동일 command ID 중복 방지
- 비정상적으로 빠른 반복 요청 감지
- 실패한 명령 횟수 기록
- 관리자용 행동 로그 조회
- 중요한 보상 획득 내역 기록

---

## 15. API 설계

### 인증 API

```text
POST   /api/v1/register
POST   /api/v1/login
POST   /api/v1/logout
GET    /api/v1/user
POST   /api/v1/guest
POST   /api/v1/guest/convert
```

### 월드 API

```text
GET    /api/v1/worlds
POST   /api/v1/worlds
GET    /api/v1/worlds/{world}
GET    /api/v1/worlds/{world}/state
GET    /api/v1/worlds/{world}/changes
DELETE /api/v1/worlds/{world}
```

### 건물 명령 API

```text
POST   /api/v1/worlds/{world}/buildings
PATCH  /api/v1/worlds/{world}/buildings/{building}/move
POST   /api/v1/worlds/{world}/buildings/{building}/upgrade
DELETE /api/v1/worlds/{world}/buildings/{building}
POST   /api/v1/worlds/{world}/buildings/{building}/collect
```

### 주민 명령 API

```text
POST   /api/v1/worlds/{world}/residents/{resident}/assign
POST   /api/v1/worlds/{world}/residents/{resident}/unassign
POST   /api/v1/worlds/{world}/residents/{resident}/train
```

### 사건과 퀘스트 API

```text
GET    /api/v1/worlds/{world}/events
POST   /api/v1/worlds/{world}/events/{event}/resolve
GET    /api/v1/worlds/{world}/quests
POST   /api/v1/worlds/{world}/quests/{quest}/reward
```

### 연구와 탐험 API

```text
POST   /api/v1/worlds/{world}/research
POST   /api/v1/worlds/{world}/research/{research}/complete
POST   /api/v1/worlds/{world}/expeditions
POST   /api/v1/worlds/{world}/expeditions/{expedition}/return
```

### 운영 API

```text
GET    /api/v1/game-config
GET    /api/v1/notices
GET    /api/v1/version
```

---

## 16. 화면 구성

### 타이틀 화면

- 게임 로고
- 로그인
- 회원 가입
- 게스트 시작
- 이어하기
- 설정
- 공지사항

### 메인 게임 화면

#### 상단

- 금화
- 식량
- 목재
- 광석
- 마나
- 도시 레벨
- 서버 연결 상태

#### 왼쪽

- 메인 퀘스트
- 일일 퀘스트
- 현재 사건
- 주민 요청

#### 오른쪽

- 건설
- 주민
- 연구
- 탐험
- 마왕 능력
- 도시 정보

#### 중앙

PixiJS 도시 화면

#### 하단

선택한 건물 또는 주민의 상세 정보

### 서버 상태 표시

- 녹색: 서버 동기화 완료
- 노란색: 명령 처리 중
- 주황색: 재연결 중
- 빨간색: 연결 끊김 또는 서버 오류

---

## 17. MVP 범위

### 계정

- 회원 가입
- 로그인
- 로그아웃
- 게스트 플레이
- 게스트 월드의 계정 이전
- 서버 세션 인증

### 도시

- `20×20` 맵
- 카메라 이동
- 확대와 축소
- 건물 배치
- 건물 이동
- 건물 철거
- 건물 업그레이드
- 서버 좌표 검증

### 건물 10종

- 마왕성
- 주택
- 농장
- 벌목장
- 광산
- 창고
- 시장
- 대장간
- 연구소
- 주점

### 주민 3종

- 슬라임
- 고블린
- 오우거

### 자원 5종

- 금화
- 식량
- 목재
- 광석
- 마나

### 콘텐츠

- 기본 퀘스트 20개
- 무작위 사건 10개
- 건물 시너지 5종
- 첫 번째 구역 확장
- 첫 번째 마왕성 복구
- 서버 시간 기반 생산
- 오프라인 생산
- 오프라인 결과 보고서

### 서버

- Laravel 인증
- MariaDB 월드 생성
- 관계형 게임 데이터 저장
- 서버 명령 검증
- 트랜잭션 처리
- revision 충돌 처리
- 중복 command ID 방지
- 오프라인 시간 계산
- 행동 로그
- 백업 스냅샷
- 연결 복구와 상태 재조회

---

## 18. 개발 단계

### 1단계: 프로젝트 기반

- Vite + Vue + TypeScript 프로젝트 생성
- Pinia와 Vue Router 설정
- PixiJS Application 연결
- Laravel 프로젝트 생성
- MariaDB 연결
- Sanctum 인증 설정
- 프론트엔드와 API 통신 확인

### 2단계: 데이터베이스 기반

- 사용자와 게스트 세션 테이블
- 월드와 자원 테이블
- 타일과 건물 테이블
- 주민 테이블
- 게임 정의 테이블
- 명령과 행동 로그 테이블
- 외래 키와 고유 인덱스 설정
- 테스트용 Seeder 작성

### 3단계: 도시 렌더링

- 타일 맵 출력
- 카메라 이동
- 확대와 축소
- 건물 배치 미리 보기
- 건물 선택
- 레이어 정렬
- 서버 상태를 기준으로 도시 구성

### 4단계: 서버 명령 시스템

- 명령 DTO와 Request
- 명령 Handler
- 게임 규칙 Validator
- 트랜잭션 처리
- 월드 행 잠금
- revision 검사
- command ID 중복 방지
- 공통 오류 응답

### 5단계: 건설과 자원

- 건물 배치 명령
- 건물 이동과 철거
- 자원 차감
- 건물 업그레이드
- 생산 시간 계산
- 생산품 수거
- 창고 용량 제한
- 건물 시너지

### 6단계: 주민

- 주민 생성
- 주민 배치
- 생산 보너스
- 주민 상태
- 주민 이동 연출
- 사고 발생 조건

### 7단계: 콘텐츠

- 퀘스트
- 사건 선택
- 연구
- 탐험
- 구역 확장
- 마왕성 복구

### 8단계: 오프라인과 복구

- 서버 시간 동기화
- 오프라인 생산 계산
- 재접속 시 최신 상태 조회
- revision 변경분 동기화
- 월드 백업 스냅샷
- 오류와 재연결 UI

### 9단계: 최적화와 운영

- API 응답 최적화
- 필요한 변경분만 반환
- MariaDB 인덱스 점검
- 느린 쿼리 점검
- Redis 캐시 선택 적용
- 큐 기반 장기 작업
- 요청 속도 제한
- 관리자 행동 로그
- 자동 백업

---

## 19. 첫 번째 플레이 가능 버전

첫 번째 플레이 가능 버전의 목표는 다음과 같다.

1. 사용자가 회원 가입 또는 게스트 시작을 한다.
2. 서버에 새로운 마왕국이 생성된다.
3. MariaDB에 월드와 초기 자원이 저장된다.
4. 폐허가 있는 `20×20` 도시가 표시된다.
5. 주택, 농장, 벌목장을 배치한다.
6. 서버가 좌표와 건설 비용을 검증한다.
7. 슬라임과 고블린이 건물 사이를 이동한다.
8. 서버 시간 기준으로 식량과 목재가 생산된다.
9. 생산품을 수거하면 서버가 결과를 검증한다.
10. 건물을 업그레이드한다.
11. 간단한 주민 사고가 발생한다.
12. 모든 행동이 즉시 MariaDB에 반영된다.
13. 브라우저를 닫고 다시 접속해도 도시가 복원된다.
14. 접속하지 않은 시간의 생산 결과가 표시된다.
15. 브라우저 데이터를 조작해도 서버 자원이 변경되지 않는다.

이 단계에서는 콘텐츠 양보다 다음 세 가지를 먼저 검증한다.

- 도시를 건설하고 성장시키는 과정이 재미있는가
- 주민이 살아 움직이는 느낌이 드는가
- 서버 데이터가 안전하고 일관되게 유지되는가

---

## 20. 최종 개발 원칙

1. Vue는 게임 인터페이스를 담당한다.
2. PixiJS는 도시 화면의 렌더링을 담당한다.
3. Pinia는 서버 데이터를 표시하기 위한 메모리 상태만 관리한다.
4. IndexedDB에 게임 데이터를 저장하지 않는다.
5. 클라이언트가 보낸 결과값을 신뢰하지 않는다.
6. 클라이언트는 행동 의도만 서버에 전달한다.
7. Laravel이 게임 규칙과 결과를 계산한다.
8. MariaDB를 모든 핵심 게임 데이터의 원본으로 사용한다.
9. 모든 중요한 변경은 트랜잭션으로 처리한다.
10. 월드마다 revision을 두어 동시 변경을 통제한다.
11. 모든 명령에 고유한 command ID를 사용한다.
12. 생산과 오프라인 진행은 서버 시간을 기준으로 계산한다.
13. JSON 스냅샷은 복구용 백업으로만 사용한다.
14. 게임 데이터베이스는 외부에 직접 노출하지 않는다.
15. 초기 목표는 콘텐츠 양보다 성장의 손맛과 데이터 안정성이다.
