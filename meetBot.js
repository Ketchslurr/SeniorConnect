const puppeteer = require('puppeteer');
require('dotenv').config();

const GOOGLE_MEET_LINK = process.env.MEET_LINK; // Load the Meet link from .env
const GOOGLE_EMAIL = process.env.GOOGLE_EMAIL;
const GOOGLE_PASSWORD = process.env.GOOGLE_PASSWORD;

async function joinMeeting() {
    const browser = await puppeteer.launch({
        headless: false,  // Set to false to see the browser (debugging)
        args: [
            '--use-fake-ui-for-media-stream', // Fake camera/mic
            '--disable-notifications'
        ]
    });

    const page = await browser.newPage();
    await page.goto('https://accounts.google.com/signin');

    // Log in to Google
    await page.type('input[type="email"]', GOOGLE_EMAIL);
    await page.click('#identifierNext');
    await page.waitForTimeout(3000);
    await page.type('input[type="password"]', GOOGLE_PASSWORD);
    await page.click('#passwordNext');
    await page.waitForNavigation();

    // Navigate to Google Meet
    await page.goto(GOOGLE_MEET_LINK, { waitUntil: 'networkidle2' });

    // Click "Join now" button
    await page.waitForSelector('button[jsname="Qx7uuf"]', { visible: true });
    await page.click('button[jsname="Qx7uuf"]');

    console.log("✅ Joined the Google Meet!");

    // Monitor for "Someone wants to join" pop-up
    setInterval(async () => {
        try {
            const admitButton = await page.$('button[jsname="Qx7uuf"]');
            if (admitButton) {
                await admitButton.click();
                console.log("✅ Admitted a participant!");
            }
        } catch (err) {
            console.error("Error checking for admit button:", err);
        }
    }, 5000); // Check every 5 seconds

    return browser;
}

// Start the bot
joinMeeting().catch(console.error);
