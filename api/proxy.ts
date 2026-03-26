export default async function handler(req: any, res: any) {
  // 🔥 CORS LIBERADO
  res.setHeader("Access-Control-Allow-Origin", "*");
  res.setHeader("Access-Control-Allow-Methods", "GET,OPTIONS");
  res.setHeader("Access-Control-Allow-Headers", "*");

  // 🔥 Preflight
  if (req.method === "OPTIONS") {
    return res.status(200).end();
  }

  const url = "https://tvonlinehd.com.br/channels.php";

  try {
    console.log("🔍 Buscando:", url);

    const response = await fetch(url, {
      headers: {
        "User-Agent": "Mozilla/5.0",
        "Accept": "application/json"
      }
    });

    const text = await response.text();

    if (!response.ok) {
      console.error("❌ Erro HTTP:", response.status);
      return res.status(response.status).json({
        error: true,
        status: response.status,
        body: text.slice(0, 300)
      });
    }

    try {
      const json = JSON.parse(text);
      return res.status(200).json(json);
    } catch {
      return res.status(500).json({
        error: true,
        type: "INVALID_JSON",
        raw: text.slice(0, 300)
      });
    }

  } catch (err: any) {
    console.error("💥 ERRO:", err.message);

    return res.status(500).json({
      error: true,
      message: err.message
    });
  }
}
