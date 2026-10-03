# Chay Teamobi2026 bang Docker

## Chay lan dau

1. Cai Docker Desktop va mo Docker Desktop.
2. Sao chep `.env.example` thanh `.env`: `Copy-Item .env.example .env`.
3. Sua `SERVER_IP`, hai mat khau database va (neu can) `DOCKER_IMAGE` trong `.env`.
4. Tai thu muc nay, chay:

```powershell
docker compose up -d --build
docker compose logs -f game-server
```

Server game su dung TCP port `14445` theo mac dinh. Can mo port nay tren firewall/router/VPS. MariaDB khong duoc public ra may host. Log duoc luu trong Docker volume `game-logs` va co the xem bang lenh `docker compose logs`.

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
docker compose build game-server
docker compose push game-server
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

Khong ghi token truc tiep vao workflow, `.env` hay bat ky file nao duoc commit. Image se duoc push toi `DOCKERHUB_USERNAME/teamobi2026` voi cac tag nhu `latest`, ten nhanh, tag phien ban va `sha-...`.
