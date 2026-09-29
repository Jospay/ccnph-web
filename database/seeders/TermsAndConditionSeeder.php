<?php

namespace Database\Seeders;

use App\Models\TermsAndCondition;
use Illuminate\Database\Seeder;

class TermsAndConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TermsAndCondition::updateOrCreate(
            [
                'name' => 'Terms and Conditions of Use',
            ],
            [
                'content' => <<<'TEXT'
<style>
  h4 {
    margin-top: 20px;
    margin-bottom: 20px;
    font-family: Arial, sans-serif;
    font-size: 1rem;
    font-weight: normal;
  }

  h4 strong,
  h4 b,
  h4 span.bold {
    font-weight: 700;
  }
</style>

<!-- HTML Structure applied to your text -->

<h4>
  <span class="bold">
    COOPERATIVES COOPERATION NETWORK PHILIPPINES (CCNPH) DIGITAL PLATFORM
  </span>
</h4>

<h4><span class="bold">1. Introduction</span></h4>

<h4>
  Welcome to the COOPERATIVES COOPERATION NETWORK PHILIPPINES (CCNPH) Digital Platform ("Platform"), owned, operated, and managed by the COOPERATIVES COOPERATION NETWORK PHILIPPINES (CCNPH) ("CCNPH", "the Cooperative", "we", "our", or "us").
</h4>

<h4>
  These Terms and Conditions of Use govern your registration, access to, and use of the CCNPH Digital Platform, including its mobile application and the services currently made available through the Platform.
</h4>

<h4>
  The Platform is designed to provide Cooperative members, applicants, and authorized users with convenient access to Cooperative information, training, membership services, payment facilities, wallet services, and other authorized digital services.
</h4>

<h4>
  By creating an account or using the Platform, you agree to comply with these Terms and Conditions, the CCNPH Privacy Policy, applicable Cooperative policies, and applicable laws of the Republic of the Philippines.
</h4>

<h4><span class="bold">2. Current Platform Services</span></h4>

<h4>
  The Platform currently provides the following services and modules:
</h4>

<h4>
  <span class="bold">News and Events</span>&nbsp;– access to Cooperative news, announcements, activities, and events.
</h4>

<h4>
  <span class="bold">Business Training</span>&nbsp;– access to business training materials, programs, and other training services made available by CCNPH.
</h4>

<h4>
  <span class="bold">Cooperative Membership</span>&nbsp;– membership application, membership verification, membership payment, membership status, and related Cooperative information.
</h4>

<h4>
  <span class="bold">Cooperative Financial Transparency</span>&nbsp;– information provided by CCNPH regarding the allocation and use of Cooperative funds and other financial information made available to members through the Platform.
</h4>

<h4>
  <span class="bold">Digital Wallet</span>&nbsp;– wallet functionality available to eligible Cooperative members for authorized transactions within the Platform.
</h4>

<h4>
  <span class="bold">Online Payments</span>&nbsp;– payment processing for applicable Cooperative services through supported payment providers, including PayMongo.
</h4>

<h4>
  <span class="bold">Member Profile</span>&nbsp;– management and verification of member information and identification documents.
</h4>

<h4>
  <span class="bold">Support and Communication</span>&nbsp;– available support, messaging, and information channels provided by CCNPH.
</h4>

<h4>
  CCNPH may introduce additional services or modules in the future. New services may be subject to additional terms, requirements, policies, or agreements.
</h4>

<h4><span class="bold">3. Account Registration and Verification</span></h4>

<h4>
  To use certain Platform services, users must create an account by providing the information requested by CCNPH.
</h4>

<h4>
  The registration process may include: Providing the user's full name and mobile number; Verification of the mobile number through a One-Time Password (OTP); Creation of a secure password; Acceptance of these Terms and Conditions; Completion of the user's profile; Submission of required personal information; and Submission of required identification information and identification documents.
</h4>

<h4>
  Certain information and documents may be reviewed manually by authorized CCNPH personnel before an account or profile is approved.
</h4>

<h4>
  Users are responsible for providing accurate, complete, and truthful information.
</h4>

<h4>
  CCNPH may reject, suspend, restrict, or request additional information regarding an account if submitted information cannot be verified or is found to be inaccurate, fraudulent, or inconsistent with Cooperative requirements.
</h4>

<h4><span class="bold">4. Member Profile Verification</span></h4>

<h4>
  Certain Platform services may require completion and approval of the user's member profile.
</h4>

<h4>
  The verification process may require information such as: Email address; Gender; Date of birth; Address; Type of identification; Identification number; and Front and back images of the required identification document.
</h4>

<h4>
  Submitted information and identification documents may be reviewed manually by authorized CCNPH personnel.
</h4>

<h4>
  Access to certain services may remain restricted until the user's profile has been successfully reviewed and approved.
</h4>

<h4>
  Users agree to provide authentic and valid information and documents. Submission of false, altered, fraudulent, or misleading information may result in account restriction, rejection, suspension, or termination.
</h4>

<h4><span class="bold">5. News and Events</span></h4>

<h4>
  The News and Events module provides users with Cooperative announcements, news, activities, events, and other official information published by CCNPH.
</h4>

<h4>
  CCNPH may update, modify, remove, or replace news and event content at any time.
</h4>

<h4>
  Information published through the Platform is intended to provide users with official Cooperative information and updates.
</h4>

<h4><span class="bold">6. Business Training</span></h4>

<h4>
  The Business Training module provides eligible users with access to training materials, programs, resources, and other business-related educational content made available by CCNPH.
</h4>

<h4>
  Access to certain training services may depend on the user's account status, profile verification, and Cooperative membership status.
</h4>

<h4>
  CCNPH may modify, add, remove, or update training programs and materials at any time.
</h4>

<h4><span class="bold">7. Cooperative Membership</span></h4>

<h4>
  Users may apply for Cooperative membership through the Platform, subject to CCNPH membership requirements and applicable Cooperative policies.
</h4>

<h4>
  Membership approval is subject to the requirements, verification procedures, and approval processes established by CCNPH.
</h4>

<h4>
  Payment of a membership fee does not automatically guarantee membership approval unless the applicable Cooperative membership process has been successfully completed.
</h4>

<h4>
  Once membership is approved and activated, eligible users may gain access to member-only services and features available through the Platform.
</h4>

<h4><span class="bold">8. Membership Payments</span></h4>

<h4>
  CCNPH may provide users with payment options for applicable membership fees.
</h4>

<h4>
  Depending on the membership payment configuration established by CCNPH, users may be offered: One-time payment; or Monthly installment payment.
</h4>

<h4>
  The available installment period, payment amount, number of installments, and other applicable conditions may be determined by CCNPH and may vary depending on the current Cooperative membership policies.
</h4>

<h4>
  Users are responsible for completing their required payments according to the selected payment arrangement.
</h4>

<h4>
  Membership payment transactions are subject to successful payment confirmation.
</h4>

<h4><span class="bold">9. PayMongo Online Payments</span></h4>

<h4>
  The Platform may use PayMongo and other authorized payment providers to process applicable online payments.
</h4>

<h4>
  Users acknowledge that: Payment transactions are processed through the applicable payment provider; CCNPH does not directly collect or store complete debit card or credit card credentials through the Platform; Payment confirmation may depend on PayMongo, banks, electronic wallet providers, or other participating financial institutions; A transaction may be delayed, declined, reversed, cancelled, or otherwise affected by the payment provider, financial institution, network, internet connection, or circumstances beyond CCNPH's reasonable control; and Refunds, reversals, and payment disputes are subject to applicable CCNPH policies, payment-provider procedures, and applicable Philippine laws.
</h4>

<h4>
  Users should retain appropriate payment confirmations and transaction references for their records.
</h4>

<h4><span class="bold">10. Cooperative Digital Wallet</span></h4>

<h4>
  Eligible Cooperative members may be provided access to the CCNPH Digital Wallet after completing the applicable membership and account requirements.
</h4>

<h4>
  The Digital Wallet is intended for authorized transactions supported by CCNPH.
</h4>

<h4>
  Users acknowledge that: Wallet balances are maintained electronically within the Platform; Wallet transactions are subject to CCNPH's applicable rules and transaction limits; Wallet funds may only be used for authorized transactions supported by the Platform; Wallet balances may be used for eligible payments or transfers supported by CCNPH; Wallet transactions may be subject to verification, transaction limits, security controls, and applicable fees, if any; Wallet balances are not bank deposits; and Wallet balances are not represented as deposits insured by the Philippine Deposit Insurance Corporation (PDIC).
</h4>

<h4>
  CCNPH may temporarily restrict or suspend wallet functionality where necessary to investigate suspected fraud, unauthorized transactions, security issues, violations of these Terms, or violations of applicable law.
</h4>

<h4><span class="bold">11. Cooperative Financial Transparency</span></h4>

<h4>
  The Platform may provide eligible members with information regarding the allocation and use of Cooperative funds.
</h4>

<h4>
  The transparency feature is intended to provide members with visibility into financial allocations and other Cooperative information made available by CCNPH.
</h4>

<h4>
  The information presented through the Platform is based on records and information made available by CCNPH and may be updated periodically.
</h4>

<h4>
  The transparency feature does not constitute an independent audit, financial guarantee, investment recommendation, or guarantee of future Cooperative financial performance.
</h4>

<h4><span class="bold">12. Electronic Consent</span></h4>

<h4>
  By selecting "I Agree," "Accept," "Register," "Submit," "Proceed," or another similar confirmation button, or by continuing to use the Platform after being presented with applicable terms, the user provides electronic consent to the applicable agreement.
</h4>

<h4>
  Electronic records, confirmations, approvals, and other electronic transactions made through the Platform may be used as evidence of the user's actions and consent, subject to applicable Philippine laws, including the Electronic Commerce Act of 2000 (Republic Act No. 8792) and other applicable laws and regulations.
</h4>

<h4><span class="bold">13. Account Security and User Responsibilities</span></h4>

<h4>
  Users are responsible for maintaining the confidentiality of their account credentials, including their password and other authentication information.
</h4>

<h4>
  Users must not: Share their password with another person; Allow unauthorized individuals to access their account; Provide false or misleading information; Use another person's account; Attempt to access another user's information or wallet; Conduct unauthorized transactions; Attempt to manipulate or interfere with Platform systems; or Use the Platform for unlawful or fraudulent activities.
</h4>

<h4>
  Users must immediately notify CCNPH through its available support channels if they suspect unauthorized access or activity involving their account.
</h4>

<h4><span class="bold">14. Fraud and Prohibited Activities</span></h4>

<h4>
  CCNPH may investigate and take appropriate action against accounts involved in suspected fraudulent, unauthorized, abusive, or unlawful activities.
</h4>

<h4>
  Prohibited activities may include: Identity theft; Submission of false membership information; Submission of fraudulent identification documents; Unauthorized account access; Payment fraud; Wallet abuse; Unauthorized transactions; Attempts to circumvent Platform security; Cybercrime; or Other activities prohibited under applicable Philippine laws.
</h4>

<h4>
  CCNPH may suspend, restrict, or terminate an account when reasonably necessary to protect users, the Cooperative, its systems, or its financial and operational interests.
</h4>

<h4>
  CCNPH may cooperate with appropriate authorities when required or permitted by applicable law.
</h4>

<h4><span class="bold">15. Service Availability</span></h4>

<h4>
  CCNPH will make reasonable efforts to maintain the availability and security of the Platform.
</h4>

<h4>
  However, services may occasionally be unavailable because of: System maintenance; Updates; Technical problems; Internet or telecommunications interruptions; Payment-provider interruptions; Security incidents; Third-party service interruptions; or Circumstances beyond the reasonable control of CCNPH.
</h4>

<h4>
  CCNPH may modify, suspend, or discontinue any Platform feature or service when reasonably necessary.
</h4>

<h4><span class="bold">16. Future Services and Modules</span></h4>

<h4>
  CCNPH may introduce additional modules, features, services, payment options, or other digital facilities in future versions of the Platform.
</h4>

<h4>
  Future services may include services that are not currently available at the time these Terms are accepted.
</h4>

<h4>
  Any future service that requires additional terms, conditions, agreements, eligibility requirements, or disclosures may be subject to those additional requirements.
</h4>

<h4>
  The introduction of a new module does not automatically make the service available to every user. Access may depend on account status, membership status, verification, eligibility, Cooperative policies, and applicable requirements.
</h4>

<h4><span class="bold">17. Privacy</span></h4>

<h4>
  CCNPH collects and processes personal information necessary to provide the Platform's services, verify user accounts, process applicable transactions, maintain security, and fulfill its Cooperative and legal obligations.
</h4>

<h4>
  The collection, use, storage, and processing of personal information are governed by the CCNPH Privacy Policy and applicable Philippine data protection laws.
</h4>

<h4>
  Users are encouraged to review the CCNPH Privacy Policy before using the Platform.
</h4>

<h4><span class="bold">18. Changes to These Terms</span></h4>

<h4>
  CCNPH may update these Terms and Conditions from time to time to reflect changes in the Platform, Cooperative policies, services, legal requirements, or security practices.
</h4>

<h4>
  Updated Terms may be presented to users through the Platform.
</h4>

<h4>
  Where required, users may be asked to review and accept updated Terms before continuing to use affected services.
</h4>

<h4><span class="bold">19. Contact Information</span></h4>

<h4>
  COOPERATIVES COOPERATION NETWORK PHILIPPINES (CCNPH)
</h4>

<h4>
  Contact Number: +63 948 295 9414
</h4>

<h4>
  Email Address: ccnph.system@gmail.com
</h4>

<h4>
  Address: 164 Dona Lucing Ave Bacolor, Pampanga
</h4>

<h4>
  Website: https://www.ccnph.org/
</h4>

<h4><span class="bold">20. Acceptance</span></h4>

<h4>
  By creating an account, completing the registration process, accepting these Terms and Conditions, applying for Cooperative membership, making membership payments, accessing Business Training, viewing News and Events, using the Digital Wallet, accessing Cooperative financial transparency information, or otherwise using the CCNPH Digital Platform, you acknowledge that you have read, understood, and agreed to be bound by these Terms and Conditions, the CCNPH Privacy Policy, applicable Cooperative policies, and applicable laws of the Republic of the Philippines.
</h4>
TEXT,
            ]
        );
    }
}
