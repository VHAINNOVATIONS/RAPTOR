using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace gov.va.medora.mdws.dto.sm
{
    public class AttachmentTO : BaseSmTO
    {
        public string attachmentName;
        public string mimeType;
        public byte[] attachment;

        public AttachmentTO() { }

        public AttachmentTO(gov.va.medora.mdo.domain.sm.MessageAttachment messageAttachment)
        {
            if (messageAttachment == null)
            {
                return;
            }

            attachmentName = messageAttachment.AttachmentName;
            mimeType = messageAttachment.MimeType;
            attachment = messageAttachment.SmFile;
            this.id = messageAttachment.Id;
            this.oplock = messageAttachment.Oplock;
        }
    }
}