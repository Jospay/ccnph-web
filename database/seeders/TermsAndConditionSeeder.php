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
TERMS AND CONDITIONS OF USE
FILIPINO INVENTORS SOCIETY MULTI-PURPOSE COOPERATIVE (FISMPC) DIGITAL PLATFORM

1. Introduction
Welcome to the FILIPINO INVENTORS SOCIETY MULTI-PURPOSE COOPERATIVE (FISMPC) Digital Platform ("Platform"), owned, operated, and managed by the FILIPINO INVENTORS SOCIETY MULTI-PURPOSE COOPERATIVE (FISMPC) ("FISMPC", "the Cooperative", "we", "our", or "us").

These Terms and Conditions of Use govern your registration, access to, and continued use of the FISMPC Digital Platform, including all current and future services, modules, mobile applications, websites, and online facilities offered by the Cooperative.

The Platform is designed to provide secure and convenient digital services to Cooperative members, applicants, borrowers, customers, inventors, entrepreneurs, and authorized users.

2. Scope of Services
The Platform provides access to the following current and future digital services:
- Business Training Module
- Intellectual Property Assistant
- Loan Application and Loan Management
- Digital Wallet Services
- FISMPC Online Store
- Cooperative Membership Application and Management
- News and Events
- Installment Payment Services
- Online Payments through PayMongo
- Wallet Cash-In and Wallet Payment Transactions
- Member Profile Management
- Notifications
- Messages
- Future digital services that may be introduced by FISMPC

3. Wallet Transactions
The FISMPC Digital Wallet is intended solely for authorized transactions within the Platform.

Users acknowledge and agree that:
- Wallet balances are maintained electronically.
- Wallet funds may only be used for services authorized by FISMPC.
- Cash-ins are subject to successful payment confirmation.
- Wallet funds may be used for purchases, loan payments, membership fees, training fees, and other approved Cooperative transactions.
- Wallet balances are not bank deposits and are not insured by the Philippine Deposit Insurance Corporation (PDIC).
- FISMPC reserves the right to suspend wallet services for suspected fraud, unauthorized transactions, money laundering, or violations of law.

4. PayMongo Online Payments
Users acknowledge that:
- Payments are securely processed by PayMongo.
- FISMPC does not collect or store complete debit card, credit card, or electronic payment credentials.
- Payment confirmations depend on PayMongo and participating financial institutions.
- Failed or reversed transactions due to banks, e-wallet providers, payment gateways, internet interruptions, or force majeure shall not automatically create liability on FISMPC.
- Refund requests are subject to Cooperative policies and applicable Philippine laws.

5. Loans and Obligations
Approved borrowers agree to:
- Pay all amortizations on or before the due date.
- Comply with the repayment schedule approved by the Cooperative.
- Accept penalties and charges for overdue obligations as permitted by Cooperative policies and Philippine law.
- Authorize FISMPC to apply available wallet balances toward outstanding obligations where permitted by applicable agreements and Cooperative policies.

6. Electronic Consent
By clicking "I Agree," "Accept," "Register," "Submit," "Proceed," or by continuing to use the Platform, the User provides a legally binding electronic consent under the Electronic Commerce Act of 2000 (Republic Act No. 8792).

Electronic records, digital signatures, electronic approvals, payment confirmations, and online agreements shall have the same legal force and effect as written documents signed by hand, subject to applicable Philippine laws.

7. Fraud Prevention
FISMPC maintains a zero-tolerance policy against fraud and illegal activities.

The Cooperative may suspend, investigate, restrict, or permanently terminate accounts involved in:
- Identity theft
- False loan applications
- Fake membership information
- Chargeback fraud
- Wallet abuse
- Payment fraud
- Unauthorized account access
- Money laundering
- Cybercrime
- Any activity prohibited under Philippine law

FISMPC reserves the right to cooperate with law enforcement agencies, regulatory authorities, and the courts of the Republic of the Philippines.

8. Contact Information
FILIPINO INVENTORS SOCIETY MULTI-PURPOSE COOPERATIVE (FISMPC)
- Contact Number: (02) 1234-5678.
- Email Address: info@fisinventorscoop.org
- Address: Unit 405, 4th Floor, 821 Cortes Building, EDSA, South Triangle, Quezon City, Philippines.
- Website: https://fismulticoop.org/

9. Acceptance
By creating an account, applying for Cooperative membership, using the Digital Wallet, submitting loan applications, enrolling in Business Training, requesting Intellectual Property Assistance, purchasing products from the FISMPC Online Store, making payments through PayMongo, paying through installment ("Hulugan"), or otherwise accessing or using any feature of the FILIPINO INVENTORS SOCIETY MULTI-PURPOSE COOPERATIVE (FISMPC) Digital Platform, you acknowledge that you have carefully read, understood, and voluntarily agree to be legally bound by these Terms and Conditions of Use, the Privacy Policy, Cooperative policies, and all applicable laws of the Republic of the Philippines.
TEXT,
            ]
        );
    }
}
