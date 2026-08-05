import { Application } from 'pixi.js'

export async function createPixiApp(container: HTMLElement): Promise<Application> {
  const app = new Application()

  await app.init({
    resizeTo: container,
    backgroundColor: 0x1c1c26,
    antialias: true,
  })

  container.appendChild(app.canvas)

  return app
}

export function destroyPixiApp(app: Application): void {
  app.destroy(true, { children: true })
}
