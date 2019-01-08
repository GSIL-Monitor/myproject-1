#codimg:utf-8

import smtplib
from email.MIMEText  import MIMEText
from email.utils import formataddr

my_sender = 'xiaogouzhineng@moomv.com'
my_user = ['xp@ldrobot.com', 'wf@ldrobot.com']

def sendEmail():
    ret = true
    try:
        msg = MIMEText('web服务器，硬盘使用额已超出，设定预警线，请及时做好处理方案', 'plain', 'utf-8')
        msg['From'] = formataddr(['系统', my_sender])
        msg['To'] = formataddr(['管理员', my_user])
        msg['Subject'] = 'web服务器，硬盘超额预警'

        server = smtplib.SMTP_SSL('smtp.qiye.163.com', 994)
        server.login(my_sender, 'gL6jx25yrusaqTLb')
        server.sendmail(my_sender, my_user, msg.as_string())
        server.quit()
    except Exception:
        ret = false

    return ret


if __name__ == '__main__':
    ret = sendEmail()
    if ret:
        print('ok')
    else:
        print('fail')


