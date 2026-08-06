const normalModules = import.meta.glob('../../assets/buildings/*_lv*.png', {
  eager: true,
  import: 'default',
}) as Record<string, string>

const constructionModules = import.meta.glob('../../assets/buildings/*_construction.png', {
  eager: true,
  import: 'default',
}) as Record<string, string>

const NORMAL_PATTERN = /([a-z_]+)_lv(\d+)\.png$/
const CONSTRUCTION_PATTERN = /([a-z_]+)_construction\.png$/

const normalUrlByCode = new Map<string, string>()
const constructionUrlByCode = new Map<string, string>()

for (const [path, url] of Object.entries(normalModules)) {
  const match = NORMAL_PATTERN.exec(path)
  if (!match) continue

  normalUrlByCode.set(match[1], url)
}

for (const [path, url] of Object.entries(constructionModules)) {
  const match = CONSTRUCTION_PATTERN.exec(path)
  if (!match) continue

  constructionUrlByCode.set(match[1], url)
}

export function getBuildingTextureUrl(code: string, state: string): string | undefined {
  if (state === 'constructing') {
    return constructionUrlByCode.get(code) ?? normalUrlByCode.get(code)
  }

  return normalUrlByCode.get(code)
}
