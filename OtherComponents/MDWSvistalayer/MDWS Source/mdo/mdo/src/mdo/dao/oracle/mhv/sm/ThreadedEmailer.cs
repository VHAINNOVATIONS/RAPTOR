using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.domain.sm;
using gov.va.medora.mdo.conf;
using System.ComponentModel;
using System.Diagnostics;
using System.IO;

namespace gov.va.medora.mdo.dao.oracle.mhv.sm
{
    public class ThreadedEmailer
    {
        //domain.sm.User _sender;
        bool _sendViaNewThread = false;
        MdoConfiguration _conf;
        Int32 _senderId;
        public IList<gov.va.medora.mdo.domain.sm.Addressee> Addressees { get; set; }
        String _cxnString;

        public ThreadedEmailer(String cxnString, Int32 senderId, IList<gov.va.medora.mdo.domain.sm.Addressee> addressees)
        {
            //try
            //{
            //    using (FileStream fs = new FileStream("C:\\SM_Activity_Log.txt", FileMode.Append, FileAccess.Write))
            //    {
            //        fs.Write(System.Text.Encoding.ASCII.GetBytes("Threaded Emailer Constructor"), 0, "Threaded Emailer Constructor".Length);
            //        fs.Flush();
            //    }
            //}
            //catch (Exception) { }

            try
            {
                _conf = new MdoConfiguration(true, mdo.conf.ConfigFileConstants.CONFIG_FILE_NAME);
                if (String.Equals(_conf.AllConfigs[ConfigFileConstants.SM_CONFIG_SECTION]["ThreadedSend"], "TRUE", StringComparison.CurrentCultureIgnoreCase))
                {
                    _sendViaNewThread = true;
                }
            }
            catch (Exception) { }


            _cxnString = cxnString;
            _senderId = senderId;
            // using the reference from the main thread appears to cause problems...
            this.Addressees = new List<Addressee>();
            foreach (Addressee addr in addressees)
            {
                this.Addressees.Add(new Addressee()
                {
                    Owner = new domain.sm.Clinician()
                    {
                        Id = addr.Id,
                        Oplock = addr.Owner.Oplock,
                        Email = addr.Owner.Email,
                        EmailNotification = addr.Owner.EmailNotification,
                        LastNotification = DateTime.Parse(addr.Owner.LastNotification.ToString()),
                        Duz = ((domain.sm.Clinician)addr.Owner).Duz,
                        StationNo = ((domain.sm.Clinician)addr.Owner).StationNo
                    },
                    Message = new domain.sm.Message()
                    {
                        Id = addressees[0].Message.Id,
                        MessageThread = new domain.sm.Thread() { MailGroup = new TriageGroup() { Name = addressees[0].Message.MessageThread.MailGroup.Name } }
                    }
                });
            }
        }

        public void emailAllViaConfig()
        {
            if (_sendViaNewThread)
            {
                System.Threading.Thread emailer = new System.Threading.Thread(new System.Threading.ThreadStart(emailAll));
                emailer.Start();
            }
            else
            {
                emailAll();
            }
        }

        //internal void emailAllAsync(bool withDbUpdates)
        //{
        //    if (withDbUpdates)
        //    {
        //        emailAll();
        //    }
        //    else
        //    {
        //        System.Threading.Thread emailerThread = new System.Threading.Thread(new System.Threading.ThreadStart(emailAllWithoutDbUpdates));
        //        emailerThread.Start();
        //    }
        //}

        internal void emailAllWithoutDbUpdates()
        {
            foreach (domain.sm.Addressee addr in this.Addressees)
            {
                sendEmail(addr.Owner.Email, ((domain.sm.Clinician)addr.Owner).StationNo, ((domain.sm.Clinician)addr.Owner).Duz, addr.Message.MessageThread.MailGroup.Name, "12345");
            }
        }

        // this is happening asynchronously in prod
        internal void emailAll()
        {
            //try
            //{
            //    using (FileStream fs = new FileStream("C:\\SM_Activity_Log.txt", FileMode.Append, FileAccess.Write))
            //    {
            //        fs.Write(System.Text.Encoding.ASCII.GetBytes("Calling email all..."), 0, "Calling email all...".Length);
            //        fs.Flush();
            //    }
            //}
            //catch (Exception) { }
            try
            {
                if (this.Addressees == null || this.Addressees.Count <= 0)
                {
                    // error!
                    return;
                }
                Int32 senderId = _senderId;
                Int32 recipientId = 0;
                Int32 recipientOplock = 0;
                Int32 messageId = this.Addressees[0].Message.Id; // can get this from any addressee
                String mailGroup = this.Addressees[0].Message.MessageThread.MailGroup.Name;
                String emailAddress = "";
                String recipientSiteCode = "";
                String recipientDUZ = "";


                foreach (Addressee addressee in this.Addressees)
                {
                    recipientId = addressee.Owner.Id;
                    recipientOplock = addressee.Owner.Oplock;
                    emailAddress = addressee.Owner.Email;
                    if (addressee.Owner is Clinician)
                    {
                        recipientSiteCode = ((domain.sm.Clinician)addressee.Owner).StationNo;
                        recipientDUZ = ((domain.sm.Clinician)addressee.Owner).Duz;
                    }

                    if (shouldSend(addressee.Owner))
                    {
                        sendEmailAndUpdateNotificationAndLog(senderId, recipientId, recipientOplock, recipientSiteCode, recipientDUZ, messageId, mailGroup, emailAddress);
                    }
                }
            }
            catch (Exception exc)
            {
                //using (FileStream fs = new FileStream("C:\\SM_Activity_Log.txt", FileMode.Append, FileAccess.Write))
                //{
                //    fs.Write(System.Text.Encoding.ASCII.GetBytes(exc.ToString()), 0, exc.ToString().Length);
                //    fs.Flush();
                //}
                //System.Console.WriteLine(exc.ToString());
            }
        }

        internal void sendEmailAndUpdateNotificationAndLog(Int32 senderId, Int32 recipientId, Int32 recipientOplock, String recipientSite, 
                                                            String recipientDUZ, Int32 messageId, String mailGroup, String emailAddress)
        {
            try
            {
                bool success = sendEmail(emailAddress, recipientSite, recipientDUZ, mailGroup, messageId.ToString());

                MessageActivity activity = new MessageActivity()
                {
                    Action = success ? domain.sm.enums.ActivityEnum.MDWS_NEW_MESSAGE_EMAIL_NOTIFICATION : domain.sm.enums.ActivityEnum.MDWS_EMAIL_SENT_ERROR,
                    Detail = String.Format("MOBILE_APPS_ENTRY^{0}<SenderID:<{1}>RecipientID:<{2}>MessageID:<{3}>Group:<{4}>Email:<{5}>CreatedDate:<{6}>Status:{7}>",
                        success ? "Success" : "Failed", senderId.ToString(), recipientId.ToString(), messageId.ToString(), mailGroup.ToString(), emailAddress, DateTime.UtcNow.ToString(), success ? "Success" : "Error"),
                    MessageId = messageId,
                    PerformerType = domain.sm.enums.UserTypeEnum.PATIENT,
                    UserId = senderId
                };

                //try
                //{
                //    using (FileStream fs = new FileStream("C:\\SM_Activity_Log.txt", FileMode.Append, FileAccess.Write))
                //    {
                //        fs.Write(System.Text.Encoding.ASCII.GetBytes(activity.ToString()), 0, activity.ToString().Length);
                //        fs.Flush();
                //    }
                //}
                //catch (Exception) { } 

                using (MdoOracleConnection cxn = new MdoOracleConnection(new DataSource() { ConnectionString = _cxnString }))
                {
                    new MessageActivityDao(cxn).createMessageActivity(activity);
                    if (success)
                    {
                        new UserDao(cxn).updateLastEmailNotification(new domain.sm.User() { Id = recipientId, Oplock = recipientOplock }); // need user ID and oplock only
                    }
                }
            }
            catch (Exception exc)
            {
                //try
                //{
                //    using (FileStream fs = new FileStream("C:\\SM_Activity_Log.txt", FileMode.Append, FileAccess.Write))
                //    {
                //        fs.Write(System.Text.Encoding.ASCII.GetBytes(exc.ToString()), 0, exc.ToString().Length);
                //        fs.Flush();
                //    }
                //}
                //catch (Exception) { } 
            }
        }

        internal bool shouldSend(domain.sm.User user)
        {
            if (String.IsNullOrEmpty(user.Email))
            {
                return false;
            }
            if (user.EmailNotification == domain.sm.enums.EmailNotificationEnum.NONE)
            {
                return false;
            }
            if (user.EmailNotification == domain.sm.enums.EmailNotificationEnum.ONE_DAILY && DateTime.Now.Subtract(user.LastNotification).TotalDays > 1)
            {
                return true;
            }
            if (user.EmailNotification == domain.sm.enums.EmailNotificationEnum.EACH_MESSAGE || user.EmailNotification == domain.sm.enums.EmailNotificationEnum.ON_ASSIGNMENT)
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        public bool sendEmail(string to, string addresseeSiteCode, string addresseeDuz, string triageGroupName, string smId)
        {
            //System.Diagnostics.Debug.WriteLine("Sending an email to " + to + " on behalf of SM...");
            try
            {
                //MdoConfiguration conf = new MdoConfiguration(true, mdo.conf.ConfigFileConstants.CONFIG_FILE_NAME);

                String smtpDeliveryMethod = _conf.AllConfigs[ConfigFileConstants.SM_CONFIG_SECTION][ConfigFileConstants.SM_EMAIL_DELIVERY_METHOD];
                String smtpHost = _conf.AllConfigs[ConfigFileConstants.SM_CONFIG_SECTION][ConfigFileConstants.SMTP_DOMAIN];
                Int32 smtpPort = Convert.ToInt32(_conf.AllConfigs[ConfigFileConstants.SM_CONFIG_SECTION][ConfigFileConstants.SMTP_PORT]);

                String from = _conf.AllConfigs[ConfigFileConstants.SM_CONFIG_SECTION][ConfigFileConstants.SM_EMAIL_FROM];
                String subject = _conf.AllConfigs[ConfigFileConstants.SM_CONFIG_SECTION][ConfigFileConstants.SM_EMAIL_SUBJECT];
                String urlLink = _conf.AllConfigs[ConfigFileConstants.SM_CONFIG_SECTION][ConfigFileConstants.SM_EMAIL_LINK];
                urlLink = urlLink.Replace("<STA3N>", addresseeSiteCode);
                urlLink = urlLink.Replace("<SMDUZ>", addresseeDuz);
                String body = String.Format(_conf.AllConfigs[ConfigFileConstants.SM_CONFIG_SECTION][ConfigFileConstants.SM_EMAIL_BODY],
                    triageGroupName, smId, urlLink);


                //String smtpHost = "smtp.va.gov";
                //Int32 smtpPort = 25;

                //String from = "MHV@va.gov";
                //String subject = "SM";
                //String urlLink = "https://sm-syst.myhealth.va.gov/mhv-sm-web/loginClinicianIntegration.action?station=<STA3N>&DUZ=<SMDUZ>";
                //urlLink = urlLink.Replace("<STA3N>", addresseeSiteCode);
                //urlLink = urlLink.Replace("<SMDUZ>", addresseeDuz);
                //String body = String.Format("The following Secure Message Notification for the Triage group: {0}, Message Id: {1}\r\n\r\nYou have one or more new Secure Messages waiting to be read. Please access Secure Messaging through the CPRS Tools menu to read the message(s). To change the frequency of this notification, access Secure Messaging, click on Preferences and under New Message Notification, select another option from the Notify Me: dropdown menu.\r\n\r\nGo to {2} to log into Secure Messaging. If you have problems opening the URL by clicking on it, copy and paste the entire link into your web browser's address window.\r\n\r\nPlease do not reply to this message.",
                //    triageGroupName, smId, urlLink);


                body = body.Replace("\\r\\n", new String(new char[] { '\n' }));

                System.Net.Mail.SmtpClient smtp = new System.Net.Mail.SmtpClient(smtpHost, smtpPort);

                smtp.DeliveryMethod = String.Equals("PICKUP", smtpDeliveryMethod, StringComparison.CurrentCultureIgnoreCase) ?
                    System.Net.Mail.SmtpDeliveryMethod.PickupDirectoryFromIis : System.Net.Mail.SmtpDeliveryMethod.Network;

                // this if block for debugging only!!! EmailTo shouldn't be part of production config
                if (!String.IsNullOrEmpty(_conf.AllConfigs[ConfigFileConstants.SM_CONFIG_SECTION]["EmailTo"]))
                {
                    to = _conf.AllConfigs[ConfigFileConstants.SM_CONFIG_SECTION]["EmailTo"];
                }

                System.Net.Mail.MailMessage msg = new System.Net.Mail.MailMessage(from, to, subject, body);
                msg.IsBodyHtml = false;
                msg.BodyEncoding = Encoding.ASCII;

                smtp.Send(msg);
                
                return true;
            }
            catch (System.Threading.ThreadAbortException)
            {
                //System.Diagnostics.Debug.WriteLine(tae.Message);
                return false;
            }
            catch (Exception exc)
            {
                //try
                //{
                //    using (FileStream fs = new FileStream("C:\\SM_Activity_Log.txt", FileMode.Append, FileAccess.Write))
                //    {
                //        fs.Write(System.Text.Encoding.ASCII.GetBytes(exc.ToString()), 0, exc.ToString().Length);
                //        fs.Flush();
                //    }
                //}
                //catch (Exception) { }
                return false;
            }
        }
    }
}
