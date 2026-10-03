# Chay Teamobi2026 bang Docker

## Chay lan dau

1. Cai Docker Desktop va mo Docker Desktop.
2. Sao chep `.env.example` thanh `.env`: `Copy-Item .env.example .env`.
3. Sua `SERVER_IP`, hai mat khau database va (neu can) cac ten image trong `.env`.
4. Tai thu muc nay, chay:

```powershell
docker compose up -d --build
docker compose logs -f game-server
```

Server game su dung TCP port `14445`. Website va trang quan tri mo tai `http://localhost:18081`; phpMyAdmin mo tai `http://localhost:18080`. MariaDB khong duoc public ra may host. Web va game dung chung database `team2026`.

Lan dau `web-panel` khoi dong, no tu tao cac bang phu tro cho website. Tai khoan co `is_admin = 1` trong bang `account` se duoc cap quyen super admin. Dang nhap tren website, sau do truy cap `/admin/` de vao trang quan tri.

Nhung tich hop nap the, SePay, reCAPTCHA, Telegram va SMTP deu la tuy chon. Dien bien tuong ung trong `.env` neu su dung; khong ghi khoa that vao source code.

File `database team2026.sql` chi duoc import khi volume database con trong. Nhung lan khoi dong sau se dung du lieu trong volume `database-data`.

## Dung va khoi dong lai

```powershell
docker compose stop
docker compose start
```

Dung `docker compose down` de xoa container nhung van giu database. Khong them `-v` neu muon giu du lieu.

## Day image len Docker Hub

Dang nhap, build va push image da khai bao tai `DOCKER_IMAGE`:

```powershell
docker login
docker compose build game-server web-panel
docker compose push game-server web-panel
```

Image game khong chua database dump hay mat khau. Database va mat khau duoc cap rieng luc deploy.

## Tu dong build bang GitHub Actions

Workflow `.github/workflows/docker-publish.yml` se build va push image khi:

- Push len nhanh `main` hoac `master`.
- Push tag bat dau bang `v`, vi du `v1.0.0`.
- Chay thu cong trong tab **Actions** cua GitHub.

Tao hai Repository Secrets tai **GitHub repository > Settings > Secrets and variables > Actions > New repository secret**:

- `DOCKERHUB_USERNAME`: ten dang nhap Docker Hub.
- `DOCKERHUB_TOKEN`: Docker Hub access token co quyen Read & Write.

Khong ghi token truc tiep vao workflow, `.env` hay bat ky file nao duoc commit. Workflow push ba image: `teamobi2026`, `teamobi2026-db` va `teamobi2026-web`.

## Cai tren CasaOS

Sau khi GitHub Actions build thanh cong, vao CasaOS, chon **App Store > Custom Install > Import** va tai file `casaos.yaml` len.

Truoc khi cai, sua `SERVER_IP` thanh IP LAN/IP public/domain cua may CasaOS. Game dung TCP `14445`; website mo tai HTTP `18081`; phpMyAdmin mo tai HTTP `18080`. Doi toan bo mat khau mac dinh truoc khi trien khai ra Internet.

MariaDB luu du lieu tai `/DATA/AppData/teamobi2026`. File CasaOS dung duong dan co dinh nay de tuong thich voi Custom Install, noi bien `$AppID` co the khong duoc thay the giong nhu app trong Store.

File CasaOS mac dinh dung ba image Docker Hub:

- `patcoder97/teamobi2026:latest`
- `patcoder97/teamobi2026-db:latest`
- `patcoder97/teamobi2026-web:latest`

Neu Docker Hub username khac `patcoder97`, sua ba dong `image:` trong `casaos.yaml` cho dung truoc khi import.
