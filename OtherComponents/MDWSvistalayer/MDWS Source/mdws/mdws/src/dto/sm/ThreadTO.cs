using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace gov.va.medora.mdws.dto.sm
{
    [Serializable]
    public class ThreadTO : BaseSmTO
    {
        public Int32 messageCategory;
        public string subject;
        public MessageTO[] messages;
        public AnnotationTO[] annotations;
        public TriageGroupTO mailGroup;

        public ThreadTO() { }

        public ThreadTO(gov.va.medora.mdo.domain.sm.Thread thread)
        {
            if (thread == null)
            {
                return;
            }

            id = thread.Id;
            oplock = thread.Oplock;
            mailGroup = new TriageGroupTO(thread.MailGroup);
            subject = thread.Subject;
            messageCategory = (Int32)thread.MessageCategoryType;

            if (thread.Annotations != null)
            {
                annotations = new AnnotationTO[thread.Annotations.Count];
                for (int i = 0; i < thread.Annotations.Count; i++)
                {
                    annotations[i] = new AnnotationTO(thread.Annotations[i]);
                }
            }
            if (thread.Messages != null)
            {
                messages = new MessageTO[thread.Messages.Count];
                for (int i = 0; i < thread.Messages.Count; i++)
                {
                    messages[i] = new MessageTO(thread.Messages[i]);
                }
            }
        }
    }
}