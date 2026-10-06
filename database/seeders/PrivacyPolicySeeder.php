<?php

namespace Database\Seeders;

use App\Models\PrivacyPolicy;
use Illuminate\Database\Seeder;

class PrivacyPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PrivacyPolicy::create([
            'name' => 'PRIVACY POLICY',
            'content' => '
                <div class="space-y-4">
                    <p class="font-semibold text-foreground">
                        COOPERATIVES COOPERATION NETWORK PHILIPPINES (CCNPH) DIGITAL PLATFORM
                    </p>
                    <p><strong>Last Updated:</strong> March 1, 2026</p>
                    <p>
                        The COOPERATIVES COOPERATION NETWORK PHILIPPINES ("CCNPH", "the Network", "we", "our", or "us") respects your privacy and is committed to protecting the personal information of users of the CCNPH Digital Platform ("Platform").
                    </p>
                    <p>
                        This Privacy Policy explains how CCNPH collects, uses, stores, protects, and processes personal information when you register for, access, or use the CCNPH mobile application, web portal, and related digital cooperative services.
                    </p>
                    <p>
                        By creating an account or using the Platform, you acknowledge that you have read and understood this Privacy Policy and consent to the collection and processing of your personal information in accordance with applicable laws and regulations.
                    </p>
                </div>

                <!-- Section 1 -->
                <div class="space-y-4 mt-8">
                    <h2 class="border-b border-border pb-2 text-xl font-semibold text-foreground">
                        1. Information We Collect
                    </h2>
                    <p>
                        CCNPH may collect personal information that you provide directly through the Platform or that is generated through your use of the Platform and cooperative network services.
                    </p>

                    <h3 class="mt-4 font-semibold text-foreground">1.1 Registration and Account Information</h3>
                    <p>During account registration, we may collect:</p>
                    <ul class="list-disc space-y-1 pl-6">
                        <li>Full name;</li>
                        <li>Mobile number;</li>
                        <li>One-Time Password (OTP) verification information;</li>
                        <li>Password and authentication-related information;</li>
                        <li>Account status;</li>
                        <li>Registration and verification records; and</li>
                        <li>Other information necessary to create and secure your account.</li>
                    </ul>

                    <h3 class="mt-4 font-semibold text-foreground">1.2 Member and Cooperative Profile Information</h3>
                    <p>When completing your personal or cooperative representative profile, we may collect:</p>
                    <ul class="list-disc space-y-1 pl-6">
                        <li>Email address;</li>
                        <li>Gender;</li>
                        <li>Date of birth;</li>
                        <li>Residential or mailing address;</li>
                        <li>Associated cooperative affiliation and designation;</li>
                        <li>Other profile information required by CCNPH; and</li>
                        <li>Information necessary to verify your identity and eligibility for Network services.</li>
                    </ul>

                    <h3 class="mt-4 font-semibold text-foreground">1.3 Identification Information</h3>
                    <p>For member verification and compliance, CCNPH may collect:</p>
                    <ul class="list-disc space-y-1 pl-6">
                        <li>Type of identification document;</li>
                        <li>Identification document number;</li>
                        <li>Front image of the identification document;</li>
                        <li>Back image of the identification document; and</li>
                        <li>Other information necessary for identity and cooperative verification.</li>
                    </ul>

                    <h3 class="mt-4 font-semibold text-foreground">1.4 Cooperative Network Membership Information</h3>
                    <p>When you apply for or maintain CCNPH network affiliation or cooperative membership, we may collect and process:</p>
                    <ul class="list-disc space-y-1 pl-6">
                        <li>Cooperative membership application information;</li>
                        <li>Membership status and tier;</li>
                        <li>Membership verification records;</li>
                        <li>Network dues and contribution payment information;</li>
                        <li>Payment history; and</li>
                        <li>Other information required for cooperative ecosystem administration.</li>
                    </ul>

                    <h3 class="mt-4 font-semibold text-foreground">1.5 Payment Information</h3>
                    <p>When you make payments through the Platform, we collect transaction information such as:</p>
                    <ul class="list-disc space-y-1 pl-6">
                        <li>Transaction reference numbers;</li>
                        <li>Payment amount;</li>
                        <li>Payment date and time;</li>
                        <li>Payment status and method; and</li>
                        <li>Other transaction-related information provided by payment gateways.</li>
                    </ul>

                    <h3 class="mt-4 font-semibold text-foreground">1.6 Digital Wallet and Inter-Cooperative Services</h3>
                    <p>If you use the CCNPH Digital Wallet or inter-cooperative transaction tools, we process:</p>
                    <ul class="list-disc space-y-1 pl-6">
                        <li>Wallet account information and current balances;</li>
                        <li>Transaction, transfer, and payout records; and</li>
                        <li>Transaction references and authorization logs.</li>
                    </ul>

                    <h3 class="mt-4 font-semibold text-foreground">1.7 News, Media, and Network Activity</h3>
                    <p>When interacting with Platform services, CCNPH maintains records including:</p>
                    <ul class="list-disc space-y-1 pl-6">
                        <li>Cooperative training participation;</li>
                        <li>News & Media interactions;</li>
                        <li>Ecosystem collaboration activity; and</li>
                        <li>Support requests and communications.</li>
                    </ul>

                    <h3 class="mt-4 font-semibold text-foreground">1.8 Device and Technical Information</h3>
                    <p>The Platform collects limited technical diagnostic information including:</p>
                    <ul class="list-disc space-y-1 pl-6">
                        <li>Device type, model, and operating system;</li>
                        <li>IP address and connection data;</li>
                        <li>Application versions; and</li>
                        <li>Error logs and performance metrics.</li>
                    </ul>
                </div>

                <!-- Section 2 -->
                <div class="space-y-4 mt-8">
                    <h2 class="border-b border-border pb-2 text-xl font-semibold text-foreground">
                        2. How We Use Personal Information
                    </h2>
                    <p>CCNPH uses collected personal information to:</p>
                    <ul class="list-disc space-y-1 pl-6">
                        <li>Create and manage user accounts and cooperative profiles;</li>
                        <li>Verify mobile numbers, member identities, and cooperative affiliations;</li>
                        <li>Process network memberships, ecosystem subscriptions, and transaction payments;</li>
                        <li>Operate authorized digital wallet and inter-cooperative services;</li>
                        <li>Deliver cooperative news, media updates, and event notifications;</li>
                        <li>Provide financial transparency reports and support services;</li>
                        <li>Detect and prevent fraud, security breaches, and unauthorized access;</li>
                        <li>Comply with applicable statutory regulations and cooperative governing standards.</li>
                    </ul>
                </div>

                <!-- Section 3 -->
                <div class="space-y-4 mt-8">
                    <h2 class="border-b border-border pb-2 text-xl font-semibold text-foreground">
                        3. Legal Basis for Processing
                    </h2>
                    <p>
                        CCNPH processes personal information under the legal grounds provided under the Data Privacy Act of 2012 (RA 10173), including contractual necessity, legal obligations, protection of vital cooperative interests, and explicit user consent where required.
                    </p>
                </div>

                <!-- Section 4 -->
                <div class="space-y-4 mt-8">
                    <h2 class="border-b border-border pb-2 text-xl font-semibold text-foreground">
                        4. Identification and Verification
                    </h2>
                    <p>
                        To ensure the safety of the cooperative movement, member identification and verification documents are reviewed strictly by authorized CCNPH personnel to maintain system integrity and prevent unauthorized activity.
                    </p>
                </div>

                <!-- Section 5 -->
                <div class="space-y-4 mt-8">
                    <h2 class="border-b border-border pb-2 text-xl font-semibold text-foreground">
                        5. Payment Providers and Third-Party Services
                    </h2>
                    <p>
                        CCNPH partners with trusted third-party providers (such as PayMongo, cloud hosting, and SMS gateway providers) to facilitate payment processing and infrastructure. These entities process data in accordance with their strict privacy standards and regulatory compliance requirements.
                    </p>
                </div>

                <!-- Section 6 -->
                <div class="space-y-4 mt-8">
                    <h2 class="border-b border-border pb-2 text-xl font-semibold text-foreground">
                        6. Sharing of Personal Information
                    </h2>
                    <p>
                        CCNPH does not sell personal information. We share data only with authorized staff, accredited financial/payment partners, or legal authorities when legally mandated or strictly required to deliver cooperative ecosystem services.
                    </p>
                </div>

                <!-- Section 7 -->
                <div class="space-y-4 mt-8">
                    <h2 class="border-b border-border pb-2 text-xl font-semibold text-foreground">
                        7. Protection and Security of Personal Information
                    </h2>
                    <p>
                        We enforce robust technical, physical, and organizational safeguards (including data encryption, strict access controls, and firewall protections) designed to guard against unauthorized access, loss, or misuse.
                    </p>
                </div>

                <!-- Section 8 -->
                <div class="space-y-4 mt-8">
                    <h2 class="border-b border-border pb-2 text-xl font-semibold text-foreground">
                        8. Retention of Personal Information
                    </h2>
                    <p>
                        CCNPH retains data only as long as necessary to fulfill cooperative membership requirements, support active transactions, resolve disputes, and satisfy Philippine regulatory or financial audit retention rules.
                    </p>
                </div>

                <!-- Section 9 -->
                <div class="space-y-4 mt-8">
                    <h2 class="border-b border-border pb-2 text-xl font-semibold text-foreground">
                        9. Account and Data Deletion
                    </h2>
                    <p>
                        Users may request account and personal data deletion via our support channels, subject to verification and any overriding legal or regulatory record-retention requirements under Philippine law.
                    </p>
                </div>

                <!-- Section 10 -->
                <div class="space-y-4 mt-8">
                    <h2 class="border-b border-border pb-2 text-xl font-semibold text-foreground">
                        10. User Rights
                    </h2>
                    <p>
                        Under the Data Privacy Act of 2012, users have the right to be informed, access, rectify, object to processing, erase/block data, and lodge complaints regarding their personal data processed by CCNPH.
                    </p>
                </div>

                <!-- Section 11 -->
                <div class="space-y-4 mt-8">
                    <h2 class="border-b border-border pb-2 text-xl font-semibold text-foreground">
                        11. Children\'s Privacy
                    </h2>
                    <p>
                        The CCNPH Platform is intended for individuals legally eligible to participate in cooperative initiatives and does not knowingly collect data from minors.
                    </p>
                </div>

                <!-- Section 12 -->
                <div class="space-y-4 mt-8">
                    <h2 class="border-b border-border pb-2 text-xl font-semibold text-foreground">
                        12. Cookies and Similar Technologies
                    </h2>
                    <p>
                        We use session tokens, local storage, and secure analytical mechanisms necessary to authenticate users and ensure smooth, secure operation of the Platform.
                    </p>
                </div>

                <!-- Section 13 -->
                <div class="space-y-4 mt-8">
                    <h2 class="border-b border-border pb-2 text-xl font-semibold text-foreground">
                        13. Third-Party Links and Services
                    </h2>
                    <p>
                        CCNPH is not responsible for the privacy practices of external third-party websites or services linked to or from our Platform.
                    </p>
                </div>

                <!-- Section 14 -->
                <div class="space-y-4 mt-8">
                    <h2 class="border-b border-border pb-2 text-xl font-semibold text-foreground">
                        14. Changes to This Privacy Policy
                    </h2>
                    <p>
                        CCNPH reserves the right to amend this Privacy Policy to reflect system updates or legal changes. Notifications regarding material updates will be published on the Platform.
                    </p>
                </div>

                <!-- Section 15 -->
                <div class="space-y-4 mt-8">
                    <h2 class="border-b border-border pb-2 text-xl font-semibold text-foreground">
                        15. Contact Information
                    </h2>
                    <p>
                        For inquiries, data protection requests, or privacy concerns, you may contact our Data Protection Officer at:
                    </p>
                    <div class="mt-2 rounded-md bg-muted p-4">
                        <p class="font-semibold text-foreground">
                            COOPERATIVES COOPERATION NETWORK PHILIPPINES (CCNPH)
                        </p>
                        <p>
                            <strong>Email Address:</strong>
                            <a href="mailto:privacy@ccnph.system" class="text-primary hover:underline">
                                privacy@ccnph.system
                            </a>
                        </p>
                        <p>
                            <strong>Social Media:</strong>
                            <a href="https://x.com/CcnphSystem" target="_blank" class="text-primary hover:underline">
                                @CcnphSystem
                            </a>
                        </p>
                    </div>
                </div>

                <!-- Section 16 -->
                <div class="space-y-4 mt-8">
                    <h2 class="border-b border-border pb-2 text-xl font-semibold text-foreground">
                        16. Acceptance and Acknowledgment
                    </h2>
                    <p>
                        By using the CCNPH Digital Platform, you acknowledge that you have read and understood this Privacy Policy and agree to its terms alongside the CCNPH Terms and Conditions of Use.
                    </p>
                </div>
            ',
        ]);
    }
}